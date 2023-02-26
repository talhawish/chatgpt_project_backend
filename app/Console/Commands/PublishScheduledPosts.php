<?php

namespace App\Console\Commands;

use App\Jobs\GeneratePost;
use Carbon\Carbon;
use App\Models\Post;
use App\Jobs\PublishPost;
use App\Jobs\UpdatePost;
use Illuminate\Console\Command;
use App\Models\ImportAndGenerate;

class PublishScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish Scheduled Posts';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $posts = Post::whereNotNull('scheduled_at')
                ->where('scheduled_at', '<', Carbon::now())
                ->where('published_status', false)
                ->get();

                    
        foreach ($posts as $post) {
            $id = $post->id;
            PublishPost::dispatch($id);
            
        }

       

       
        return Command::SUCCESS;
    }
}
