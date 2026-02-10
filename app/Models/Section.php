<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'class_id', 'capacity', 'status'];
    public function class()
    {
        return $this->belongsTo(Classes::class); // replace Classes with your class model
    }
}
