<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected static ?string $heading = 'Suas vendas';
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '10s';
    protected static ?string $maxHeight = '350px';

    protected function getData(): array
    {
        // Pega o ano atual
        $year = now()->year;

        // Busca todas as vendas do ano, agrupadas por mês
        $sales = Sale::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // Preenche os meses que não têm vendas com 0
        $data = [];
        foreach (range(1, 12) as $month) {
            $data[] = $sales[$month] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Mostrar/Esconder',
                    'data' => $data,
                    'backgroundColor' => '#fbbf24',
                    'borderColor' => '#fbbf24',
                    'fill' => false, // para não preencher abaixo da linha
                ],
            ],
            'labels' => [
                'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun',
                'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
