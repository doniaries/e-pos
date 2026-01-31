<?php

namespace App\Filament\Resources\ProdukResource\Pages;

use App\Filament\Resources\ProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

use Filament\Support\Enums\MaxWidth;

use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProduks extends ListRecords
{
    protected static string $resource = ProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth(MaxWidth::SixExtraLarge)
                ->modalAutofocus(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->icon('heroicon-m-list-bullet'),
            'stok_banyak' => Tab::make('Stok Banyak')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('stok', '>', \Illuminate\Support\Facades\DB::raw('stok_minimum')))
                ->icon('heroicon-m-check-circle')
                ->badge(fn() => \App\Models\Produk::where('stok', '>', \Illuminate\Support\Facades\DB::raw('stok_minimum'))->count())
                ->badgeColor('success'),
            'stok_menipis' => Tab::make('Stok Menipis')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('stok', '<=', \Illuminate\Support\Facades\DB::raw('stok_minimum'))->where('stok', '>', 0))
                ->icon('heroicon-m-exclamation-triangle')
                ->badge(fn() => \App\Models\Produk::where('stok', '<=', \Illuminate\Support\Facades\DB::raw('stok_minimum'))->where('stok', '>', 0)->count())
                ->badgeColor('warning'),
            'stok_habis' => Tab::make('Stok Habis')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('stok', '<=', 0))
                ->icon('heroicon-m-x-circle')
                ->badge(fn() => \App\Models\Produk::where('stok', '<=', 0)->count())
                ->badgeColor('danger'),
        ];
    }
}
