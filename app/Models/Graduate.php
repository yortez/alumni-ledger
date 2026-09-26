<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['student_number', 'name', 'program', 'graduation_year'])]
class Graduate extends Model
{
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'student_number', 'student_number');
    }
}
