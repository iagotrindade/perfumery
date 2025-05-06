<?php

namespace App\Filament\Resources\BrandResource\Pages;

use Carbon\Carbon;
use Filament\Actions;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Query\Builder;
use App\Filament\Resources\BrandResource;
use Filament\Resources\Pages\ListRecords;

class ListBrands extends ListRecords
{
    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $lastMonth = Carbon::now()->subMonth();
        $oneMonthAgo = Carbon::now()->subMonth()->startOfMonth();

        return [
            'all' => Tab::make('Todas'),

            // Tab para categorias mais vendidas
            'Mais vendidas' => Tab::make('Mais vendidas')
                ->modifyQueryUsing(function ($query) use ($lastMonth) {
                    return $query->join('products', 'brands.id', '=', 'products.brand_id')
                        ->join('product_sales', 'products.id', '=', 'product_sales.product_id') // Corrigido para 'product_sales'
                        ->where('product_sales.created_at', '>=', $lastMonth) // Corrigido para 'product_sales'
                        ->select('brands.*', DB::raw('SUM(product_sales.quantity) as total_sold')) // Corrigido para 'product_sales'
                        ->groupBy('brands.id')
                        ->orderByDesc('total_sold');
                }),

            // Tab para categorias menos vendidas
            'Menos vendidas' => Tab::make('Menos vendidas')
                ->modifyQueryUsing(function ($query) use ($oneMonthAgo) {
                    return $query->join('products', 'brands.id', '=', 'products.brand_id')
                        ->join('product_sales', 'products.id', '=', 'product_sales.product_id') // Corrigido para 'product_sales'
                        ->where('product_sales.created_at', '<=', $oneMonthAgo) // Corrigido para 'product_sales'
                        ->select('brands.*', DB::raw('SUM(product_sales.quantity) as total_sold')) // Corrigido para 'product_sales'
                        ->groupBy('brands.id')
                        ->orderBy('total_sold');
                }),
        ];
    }
}
