<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class OrderProduct extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
}
