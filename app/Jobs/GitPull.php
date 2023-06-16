<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Symfony\Component\Process\Exception\ProcessFailedException;

class GitPull implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
         /**
         * You need setup the webhook in github repo settings
         * you call this job in respective route or controller method GitPull::dispatch(); to trigger the job
         * Before using this job, make sure you have set the remote origin to the correct url with your credentials
           git remote set-url origin https://{username}:{token}@github.com/{repoOwner}/{repo}
         * the following env variables are needed
            GITHUB_WEBHOOK_SECRET required to validate the request
         * make sure also you have the file pulling.sh in the root of your project
         * NPM_COMMAND if you want to specify what to do after npm install
         * The following permissions are needed for www-data user
            cd /path/to/repo/.git
            sudo chgrp -R groupname .
            sudo chmod -R g+rwX .
            sudo find . -type d -exec chmod g+s '{}' +
         */
        //testing new pull method
        $request = request();
         // 驗證 Github webhook 請求的有效性 --
        $payload = $request->getContent();
        $signature = $request->header('X-Hub-Signature');
        if (!hash_equals('sha1=' . hash_hmac('sha1', $payload, env('GITHUB_WEBHOOK_SECRET')), $signature)) {
            abort(403, 'Unauthorized action.');
            Log::info('Unauthorized action.');
        }

         // Pull
         /* putenv('PATH=/usr/local/bin'); */
         $process = new Process(['git','pull']);
         $process->setWorkingDirectory(base_path());
         $process->run();

         echo 123;

         // executes after the command finishes
         if (!$process->isSuccessful()) {
                Log::info('Error pulling from github');
             throw new ProcessFailedException($process);
         }
    }
}
