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
         * get all the env variables from .env file
         * Before using this job, make sure you have set the remote origin to the correct url with your credentials
           git remote set-url origin https://{username}:{token}@github.com/{repoOwner}/{repo}
         * the following env variables are needed
            GITHUB_WEBHOOK_SECRET required to validate the request
         * make sure also you have the file pulling.sh in the root of your project
         * NPM_COMMAND if you want to specify what to do after npm install
          It is possible that you will have to adjust many folder permissions
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
         $process = new Process(['git','pull']);
         $process->setWorkingDirectory(base_path());
         $process->run();

         // executes after the command finishes
         if (!$process->isSuccessful()) {
                Log::info('Error pulling from github');
             throw new ProcessFailedException($process);
         }

        Log::info($process->getOutput());

        // Composer install

        //set COMPOSER_HOME variable to base_path()

        $command = ['composer','install'];

        $env = [
            'COMPOSER_HOME' => base_path(),
            'PATH' => env('PULL_PATHS')
        ];

        $process = new Process($command, null, $env);
        $process->setWorkingDirectory(base_path());
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            try{
                throw new ProcessFailedException($process);
            }catch(ProcessFailedException $e){
                Log::info('Error composer install'. $e->getMessage());
            }
        }

       Log::info($process->getOutput());

       //run migration
         $command = ['php','artisan','migrate','--force'];

            $process = new Process($command);
            $process->setWorkingDirectory(base_path());
            $process->run();


             // executes after the command finishes
        if (!$process->isSuccessful()) {
            try{
                throw new ProcessFailedException($process);
            }catch(ProcessFailedException $e){
                Log::info('Error migrate'. $e->getMessage());
            }
        }

         Log::info($process->getOutput());

        //run npm install
        $command = ['npm','install'];

        $process = new Process($command, null, $env);
        $process->setWorkingDirectory(base_path());
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            try{
                throw new ProcessFailedException($process);
            }catch(ProcessFailedException $e){
                Log::info('Error npm install'. $e->getMessage());
            }
        }

        Log::info($process->getOutput());

        //run npm run production
        $command = ['npm','run','production'];


        $process = new Process($command, null, $env);
        $process->setWorkingDirectory(base_path());
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            try{
                throw new ProcessFailedException($process);
            }catch(ProcessFailedException $e){
                Log::info('Error npm production'. $e->getMessage());
            }
        }

        Log::info($process->getOutput());

        //run npm run build
        $command = ['npm','run','build'];

        $process = new Process($command, null, $env);
        $process->setWorkingDirectory(base_path());
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            try{
                throw new ProcessFailedException($process);
            }catch(ProcessFailedException $e){
                Log::info('Error npm build'. $e->getMessage());
            }
        }

        Log::info($process->getOutput());


    }
}
