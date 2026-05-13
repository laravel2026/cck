<?php

namespace Modules\Cck\Filament\Resources;

use Modules\Cck\Models\CckMenu;
use Modules\Cck\Models\CckType;
use Modules\Cck\Filament\Resources\CckMenuResource\Pages;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class CckMenuResource extends Resource
{
    protected static ?string $model = CckMenu::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationLabel = '内容菜单';

    protected static ?string $modelLabel = '菜单';

    protected static \UnitEnum|string|null $navigationGroup = 'CCK 内容管理';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label('菜单名')
                    ->required()
                    ->maxLength(200),
                Forms\Components\Select::make('parent_id')
                    ->label('所属父级菜单')
                    ->options(CckMenu::whereNull('parent_id')->pluck('name', 'id'))
                    ->placeholder('留空为顶级菜单')
                    ->live()
                    ->helperText('顶级菜单作为导航分组显示，子菜单作为可点击入口'),
                Forms\Components\TextInput::make('icon')
                    ->label('图标')
                    ->placeholder('如 heroicon-o-document-text')
                    ->helperText(new \Illuminate\Support\HtmlString('输入 Heroicons 图标名称，仅子菜单需要。<br><a href="https://heroicons.com" target="_blank" class="underline text-primary-500">打开 Heroicons 官网挑选图标</a>'))
                    ->visible(fn($get) => (bool) $get('parent_id')),
                Forms\Components\Select::make('cck_type_id')
                    ->label('关联内容类型')
                    ->options(CckType::where('is_active', true)->pluck('display_name', 'id'))
                    ->placeholder('选择要管理的内容类型')
                    ->helperText('子菜单需要选择内容类型')
                    ->visible(fn($get) => (bool) $get('parent_id')),
                Forms\Components\Toggle::make('is_active')
                    ->label('启用')
                    ->default(true),
                Forms\Components\TextInput::make('sort')
                    ->label('排序')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('菜单名')
                    ->formatStateUsing(function ($record) {
                        if ($record->parent_id) {
                            return '　　├ ' . $record->name;
                        }
                        return $record->name;
                    })
                    ->extraAttributes(fn($record) => $record->parent_id
                        ? ['class' => 'text-gray-500 dark:text-gray-400']
                        : ['class' => 'font-bold']
                    )
                    ->searchable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('父级')
                    ->formatStateUsing(fn($state) => $state ?: '—'),
                Tables\Columns\TextColumn::make('cckType.display_name')
                    ->label('内容类型')
                    ->badge()
                    ->visible(fn($record) => (bool) ($record?->cck_type_id)),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('启用')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort')
                    ->label('排序')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->width('180px')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCckMenus::route('/'),
            'create' => Pages\CreateCckMenu::route('/create'),
            'edit' => Pages\EditCckMenu::route('/{record}/edit'),
        ];
    }
}
