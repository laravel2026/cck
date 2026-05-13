<?php

namespace Modules\Cck\Filament\Resources\CckNodeResource\Pages;

use Modules\Cck\Filament\Resources\CckNodeResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListCckNodes extends ListRecords
{
    protected static string $resource = CckNodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('新建内容')
                ->url(fn() => CckNodeResource::getUrl('create', [
                    'cck_type_id' => request('cck_type_id'),
                ])),
        ];
    }
}
