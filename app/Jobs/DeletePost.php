<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Controllers\WordpressController;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class DeletePost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $wp_post_id;
    protected $wordpress;

    public $tries = 3;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($wp_post_id, $wordpress)
    {
        $this->wp_post_id = $wp_post_id;

        $this->wordpress = $wordpress;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
         
        if(!empty($this->wp_post_id)) {

            $wp = new WordpressController($this->wordpress['website_url'], $this->wordpress['username'], $this->wordpress['password']);

            $wp->deletePost($this->wp_post_id);
            
        }
 

       
        
    }
}
