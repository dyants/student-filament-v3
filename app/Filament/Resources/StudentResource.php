<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Student;

use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use App\Models\Section;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get;
use App\Filament\Resources\StudentResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\StudentResource\RelationManagers;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\BulkAction;
// dibawah ini record exel export ditambahkan
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentsExport;
use App\Imports\StudentsImport;
use App\Models\Classes;
use Illuminate\Support\Collection;
use Filament\Actions\ImportAction;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Arr;
use Filament\Tables\Actions\Action;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    // Navigation group for the resource
    protected static ?string $navigationGroup = 'Academic Management';

    // Get the number of students for navigation badge
    public static function getNavigationBadge(): ?string
    {
        return static::$model::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                 Select::make('class_id')
                    ->live()
                    ->relationship(name: 'class', titleAttribute: 'name'),

                    Select::make('section_id')
                    ->label('Section')
                    ->options(function (Get $get) {
                        $classId = $get('class_id');

                        
                        if ($classId) {
                            return Section::where('class_id', $classId)->pluck
                            ('name', 'id')->toArray();
                        }
                    }),

                TextInput::make('name')
                    ->autofocus()
                    ->required(),

                TextInput::make('email') 

                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'Maaf email ini sudah digunakan.', // Custom validasi message in Indonesian
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('class.name')->badge() ->searchable(),
                TextColumn::make('section.name')->badge(),

            ])
            ->filters([
                Filter::make('class-section-filter')
                ->form([
                    Select::make('class_id')
                        ->label('Filter by Class')
                        ->placeholder('Select a Class')
                        ->options(
                            Classes::pluck('name', 'id')->toArray(),
                        ),

                    Select::make('section_id')
                        ->label('Filter by Section')
                        ->placeholder('Select a Section')
                        ->options(function(Get $get){
                            $classId = $get('class_id');
                            if ($classId) {
                                return Section::where('class_id', $classId)
                                ->pluck('name', 'id')->toArray();
                            }
                        }),             
                    ])
                    // Apply the filter to the query
                    ->query(function (Builder $query,array $data): Builder {
                        return $query->when($data['class_id'], function($query) use($data) {
                            return $query->where('class_id', $data['class_id']);
                        })->when($data['section_id'], function($query) use($data) {
                            return $query->where('section_id', $data['section_id']);
                        });
                }),
            ])
            ->actions([
                // Generate PDF Invoice
                Action::make('downloadPdf')->url(function (Student $student) {
                    return route('students.invoice.generate', $student);
                }),

                // Generate QR Code
                Action::make('qrCode')
                ->url(function (Student $record) {
                     return static::getUrl('qrCode', ['record' => $record]);
                }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('export')
                    ->label('Export to Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function(Collection $records) {
                         return Excel::download(new StudentsExport($records), 'students.xlsx');
                    })
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
            'qrCode' => Pages\GenerateQrCode::route('/{record}/qrcode'),

        ];
    }
}
