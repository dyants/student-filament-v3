<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Classes extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    // relation to section model
    public function sections () 
    {
        return $this->hasMany(Section::class, 'class_id');
    }

    // relation to student model
    public function students ()
    {
        return $this->hasMany(Student::class, 'class_id');
    }
}
