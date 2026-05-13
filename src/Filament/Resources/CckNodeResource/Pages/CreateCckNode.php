<?php

namespace Modules\Cck\Filament\Resources\CckNodeResource\Pages;

use Modules\Cck\Filament\Resources\CckNodeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCckNode extends CreateRecord
{
    protected static string $resource = CckNodeResource::class;

    protected function afterCreate(): void
    {
        // 保存 user_id 为当前用户
        if (! $this->record->user_id) {
            $this->record->user_id = auth()->id();
            $this->record->save();
        }
    }
}
