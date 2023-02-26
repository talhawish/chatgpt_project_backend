<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Post;
use App\Models\Wordpress;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Http\Controllers\ChatgptController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Controllers\WordpressController;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\RateLimited;

class GeneratePostContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $subtitle;
    private $postId;
    private $repeats;


    public $tries = 3;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($subtitle, $postId, $repeats)
    {
        $this->subtitle = $subtitle;

        $this->postId = $postId;

        $this->repeats = $repeats;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $generatedContent = $this->generatePostContent();

        if (empty($generatedContent)) {
            throw new \Exception('Content to be regenerated ');
        }

        $post = Post::find($this->postId);

        $content = $post?->content;

        $subArticle = "<br>";


        $subArticle .= '<h5 id="' . $this->subtitle . '">' . $this->subtitle . '</h5><br>';



        $subArticle .= $generatedContent;


        $content = str_replace('[article ' . $this->repeats . ' space]', $subArticle, $content);

        if (!empty($generatedContent)) {
            Post::whereId($this->postId)->update([
                'content' => $content,
                'needs_update'  => true
            ]);
        } else {
            throw new \Exception('Content to be regenerated ');
        }
    }

    public function generatePostContent(): string
    {

        $gpt = new ChatgptController();

        $generatedContent = $gpt->generatePostContent($this->subtitle);

        if (empty($generatedContent)) {
            throw new \Exception('Content to be regenerated ');
        }

        return $generatedContent;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [new RateLimited('generatePostContent')];
    }
}