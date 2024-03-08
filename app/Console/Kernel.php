<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Plan_solution;
use App\Models\Plan_solution_order;
use App\Models\Product_solution;
use App\Models\Product_solution_order;
use App\Notifications\SolutionExpiredNotify;

class Kernel extends ConsoleKernel
{

    private function solution_expired_mail($activated_solution_orders) {
        $today = Carbon::now()->format('Y-m-d');
        $max = count($activated_solution_orders);

        for ($i = 0; $i < $max; $i++) {
            $solution_order = $activated_solution_orders[$i];

            $before_1_day = $solution_order->expired_at->subDay()->format('Y-m-d');
            $before_7_day = $solution_order->expired_at->subDay(7)->format('Y-m-d');

            if ($before_1_day == $today)
            {
                $user = $solution_order->order->user;
                $user->notify(
                    new SolutionExpiredNotify(
                        $solution_order->order_id, 
                        $solution_order->expired_at->format('Y-m-d'),
                        1
                    ));
                Log::info('send');
            }
            elseif ($before_7_day == $today)
            {
                $user = $solution_order->order->user;
                $user->notify(
                    new SolutionExpiredNotify(
                        $solution_order->order_id, 
                        $solution_order->expired_at->format('Y-m-d'),
                        7
                    ));
                Log::info('send');
            }
        }
    }

    private function check_solution($activated_solution_orders) {
        $max = count($activated_solution_orders);
        for ($i = 0; $i < $max; $i++) {
            $solution_order = $activated_solution_orders[$i];
            if (strtotime($solution_order->expired_at) < time()) {
                $solution_order->is_activated = 0;
                $solution_order->save();
                Log::info('[Order id: '.$solution_order->order_id.'][expired time: '.$solution_order->expired_at.']: has expired');
            }
        }
    }

    protected function check_plan_solution_order(Schedule $schedule) {
        $schedule->call(function () {        
            $this->check_solution(Plan_solution_order::where('is_activated', 1)->get());
        })->hourly();
    }

    protected function check_product_solution_order(Schedule $schedule) {
        $schedule->call(function () {        
            $this->check_solution(product_solution_order::where('is_activated', 1)->get());
        })->hourly();
    }

    protected function plan_solution_expired_mail(Schedule $schedule) {
        $schedule->call(function () {
            $this->solution_expired_mail(Plan_solution_order::where('is_activated', 1)->get());
        });
    }

    protected function product_solution_expired_mail(Schedule $schedule) {
        $schedule->call(function () {
            $this->solution_expired_mail(product_solution_order::where('is_activated', 1)->get());
        })->daily();
    }


    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        
        // $schedule->command('console:test')->everyMinute();

        // $this->check_plan_solution_order($schedule);
        // $this->check_product_solution_order($schedule);
        // $this->plan_solution_expired_mail($schedule);
        // $this->product_solution_expired_mail($schedule);
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
