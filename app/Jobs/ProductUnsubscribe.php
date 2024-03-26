<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use App\Models\Order;
use App\Models\Plan_solution;
use App\Models\Product_solution_order;
use App\Events\AIBoxRefresh;

class ProductUnsubscribe implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ps_order_id;
    /**
     * Create a new job instance.
     */
    public function __construct($ps_order_id)
    {
        $this->ps_order_id = $ps_order_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ps_order = product_solution_order::where('order_id', $this->ps_order_id)->first();
        $order = Order::where('id', $this->ps_order_id)->first();
        
        if (!$ps_order) {
            return;
        }

        $ps_order->is_activated = 0;
        $ps_order->save();
        
        event(new AIBoxRefresh($order->user_id));
    }
}
