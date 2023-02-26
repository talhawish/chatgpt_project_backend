<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportAndGenerate extends Model
{
    use HasFactory;
    
    protected $table = "import_and_generate";



    protected $fillable = ['website_id', 'keywords', 'matchwords', 'categories','kind', 'subtitles', 'is_generated', 'scheduled_at'];

    protected $casts = [
        'categories' => 'array',
    ];


    public function website()
    {
        return $this->belongsTo('App\Models\Wordpress');
    }



    public function post() {
        return $this->hasMany('App\Models\Post');
    }


}
