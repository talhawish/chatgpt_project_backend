<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wordpress extends Model
{
    protected $table = "wordpresses";

    protected $guarded = [''];
    protected $fillable = ['website_url', 'username', 'password', 'status'];
    
    use HasFactory;
}
