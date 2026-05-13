<?php

namespace Modules\Cck\Filament\Resources\CckMenuResource\Pages;

use Modules\Cck\Filament\Resources\CckMenuResource;
use Modules\Cck\Models\CckMenu;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;

class ListCckMenus extends ListRecords
{
    protected static string $resource = CckMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('新建菜单'),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return CckMenu::query()
            ->orderBy('parent_id')
            ->orderBy('sort');
    }
}
