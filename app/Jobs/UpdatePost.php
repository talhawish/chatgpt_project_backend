<?php

namespace App\Jobs;

use App\Models\Wordpress;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Controllers\WordpressController;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class UpdatePost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $post;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        // $data = Wordpress::find($this->post->website->id);
        
        $wp = new WordpressController($this->post->website->website_url, $this->post->website->username, $this->post->website->password);

        $wp->edit_post($this->post);

    }
}
