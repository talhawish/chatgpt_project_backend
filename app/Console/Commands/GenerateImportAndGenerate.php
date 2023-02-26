<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Jobs\GeneratePost;
use Illuminate\Console\Command;
use App\Models\ImportAndGenerate;

class GenerateImportAndGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importAndGenerateeee:generateee';

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


        // $generates = ImportAndGenerate::whereNotNull('scheduled_at')
        // ->where('scheduled_at', '<', Carbon::now())
        // ->where('scheduled_status', false)
        // ->get();


        // foreach ($generates as $generate) {
        //     GeneratePost::dispatch($generate);
        // }
        


        return Command::SUCCESS;
    }
}
