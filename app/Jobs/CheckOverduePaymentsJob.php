<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\SaleInstallment;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Http\Controllers\ReportController;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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

        // Adiciona um LOG
        Log::info('Verificação de pagamentos vencidos realizada em ' . Carbon::now()->toDateTimeString());
    }
}
