<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Post;
use App\Jobs\UpdatePost;
use Illuminate\Console\Command;

class UpdatePosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $posts = Post::whereNotNull('wp_post_id')
        ->where('needs_update', 1)
        ->where('published_status', true)
        ->get();

        $seconds = 10;

        foreach ($posts as $post) {

            $update = (new UpdatePost($post))->delay(Carbon::now()->addSeconds($seconds));
            dispatch($update);

            $seconds += 3;
            
        }





        return Command::SUCCESS;
    }
}
