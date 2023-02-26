<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use App\Models\ImportAndGenerate;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use App\Http\Controllers\ChatgptController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class GeneratePost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $data = [];

    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $gpt = new ChatgptController();


        // $post = $gpt->generatePost($this->data);

        $post = $gpt->generatePost($this->data);

        if (!is_array($post)) {
            throw new \Exception('Generated Post was wrong structured ');
        }

        $title = $post['title'];

        if (empty($title)) {
            throw new \Exception('Title to be regenerated ');
        }

        $articles = $post['articles'];


        $count = 1;

        $content = "<p>" . $post['titleDescription'] . " </p><br>";

        $content .= "<h4>Inhaltsangabe: </h4>";
        $content .= "<ul style='list-style-type: none'>";

        foreach ($articles as $article) {
            if (empty($article)) {
                continue;
            }

            $article = $gpt->clean($article);

            $content .= '<li><h5><a href="#' . $article . '">' . $article . '</a></h5></li>';

            $count++;
        }
        $content .= "</ul>";
        $content .= "<br>";

        for ($i = 1; $i <= count($articles); $i++) {

            $content .= '[article ' . $i . ' space] <br>';
        }
        $regenerate = isset($this->data['regenerate']) ? $this->data['regenerate'] : false;

        // Ceate a post without content

        $post = new Post();
        $post->title = $title;
        $post->website_id = $this->data['website_id'];
        $post->content = $content;
        // $post->scheduled_at = Carbon::now();
        $post->categories = $this->data['categories'];
        $post->import_and_generate_id = $this?->data['id'];
        $post->scheduled_at = isset($this->data['scheduled_at']) ? $this->data['scheduled_at'] : null;
        $post->tags = "";
        $post->save();


        ImportAndGenerate::whereId($this->data['id'])->update(['is_generated' => true, 'scheduled_status' => true]);




        $seconds = 10;
        $repeats = 1;


        foreach ($articles as $article) {

            if (empty($article)) {
                continue;
            }

            $article = $gpt->clean($article);

            $content = (new GeneratePostContent($article, $post->id, $repeats))->delay(Carbon::now()->addSeconds($seconds));

            dispatch($content);

            $seconds += 15;
            $repeats += 1;
        }


        $update = (new UpdatePost($post->refresh()))->delay(Carbon::now()->addSeconds($seconds + 200));

        dispatch($update);
    }


    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [new RateLimited('generatePost')];
    }
}