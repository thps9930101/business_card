<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Order;
use App\Models\Plan_solution;
use App\Models\Plan_solution_order;

class PlanUnsubscribe implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $plan_order_id;
    /**
     * Create a new job instance.
     */
    public function __construct($plan_order_id)
    {
        $this->plan_order_id = $plan_order_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $plan_order = Plan_solution_order::where('order_id', $this->plan_order_id)->first();
        $order = Order::where('id', $this->plan_order_id)->first();
        
        if (!$plan_order) {
            return;
        }

        $plan_order->is_activated = 0;
        $plan_order->save();
    }
}
