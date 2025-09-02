<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Events\OrderStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderStatusUpdated $event): void
    {
        //
    }
}
