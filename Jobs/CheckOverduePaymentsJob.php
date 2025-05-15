<?php

namespace App\Jobs;

use App\Models\SaleInstallment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Http\Controllers\ReportController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Carbon\Carbon;

class CheckOverduePaymentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $today = now()->format('Y-m-d');

        SaleInstallment::where('status', 'pending')
            ->whereDate('due_date', '<', $today)
            ->update(['status' => 'overdue']);
    }
}
