<?php

namespace Modules\Cck\Filament\Resources\CckTypeResource\Pages;

use Modules\Cck\Filament\Resources\CckTypeResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListCckTypes extends ListRecords
{
    protected static string $resource = CckTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('新建内容类型'),
        ];
    }
}
