<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    use HasFactory;

    protected $table = "animals";

    protected $fillable = ["type", "pos_x", "pos_y"];

    public $timestamps = false;
}
