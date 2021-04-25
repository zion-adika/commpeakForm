<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class calls extends Model
{
    use HasFactory;
    public $fillable = ['customer_id', 'num_calls_contiinent', 'total_duration_continent','total_calls', 'total_duration'];
}

