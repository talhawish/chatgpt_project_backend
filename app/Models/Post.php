<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['website_id', 'title', 'content', 'categories','comment_status', 'ping_status', 'tags',  'meta_title',
     'meta_title', 'meta_description', 'excerpt', 'status', 'slug', 'keywords', 'matchwords', 'kind', 'number_of_articles', 'import_and_generate_id',
      'scheduled_at', 'published_status', 'needs_update'];

    protected $casts = [
        'categories' => 'array',
        // 'comment_status' => 'array',
        'tags' => 'array',
        // 'meta_title' => 'array',
        // 'meta_description' => 'array',
    ];


    use HasFactory;


    public static function boot()
{
     parent::boot();
     
}


    public function website()
    {
        return $this->belongsTo('App\Models\Wordpress', 'website_id', 'id');
    }

    public function importAndGenerate()
    {
        return $this->belongsTo('App\Models\ImportAndGenerate', 'import_and_generate_id', 'id');
    }

    // public function categories()
    // {
    //     return $this->belongsToMany('App\Models\Category');
    // }
}
