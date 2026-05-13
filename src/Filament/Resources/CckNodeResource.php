<?php

namespace Modules\Cck\Filament\Resources;

use Modules\Cck\Models\CckNode;
use Modules\Cck\Models\CckType;
use Modules\Cck\Filament\Resources\CckNodeResource\Pages;
use Modules\Cck\Lib\FieldSchemaBuilder;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Navigation\NavigationItem;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

class CckNodeResource extends Resource
{
    protected static ?string $model = CckNode::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = '内容管理';

    protected static ?string $modelLabel = '内容';

    protected static \UnitEnum|string|null $navigationGroup = 'CCK 内容管理';

    protected static ?int $navigationSort = 2;

    /**
     * 当通过菜单（带 cck_type_id 参数）进入时，不让"内容管理"导航项高亮
     */
    public static function getNavigationItems(): array
    {
        if (! static::hasPage('index')) {
            return [];
        }

        return [
            NavigationItem::make(static::getNavigationLabel())
                ->group(static::getNavigationGroup())
                ->parentItem(static::getNavigationParentItem())
                ->icon(static::getNavigationIcon())
                ->activeIcon(static::getActiveNavigationIcon())
                ->isActiveWhen(fn (): bool => ! request('cck_type_id') && request()->routeIs(static::getNavigationItemActiveRoutePattern()))
                ->sort(static::getNavigationSort())
                ->badge(static::getNavigationBadge(), color: static::getNavigationBadgeColor())
                ->url(static::getNavigationUrl()),
        ];
    }

    /**
     * 动态表单：根据内容类型构建表单字段
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components(function (?CckNode $record) {
                $components = [];
                $defaultTypeId = $record?->cck_type_id ?: request('cck_type_id');

                // 内容类型放在上面
                if ($record) {
                    // 编辑模式 - 只读显示类型
                    $components[] = Forms\Components\Select::make('cck_type_id')
                        ->label('内容类型')
                        ->options(CckType::pluck('display_name', 'id'))
                        ->disabled()
                        ->default($defaultTypeId);
                } else {
                    // 新建模式
                    $components[] = Forms\Components\Select::make('cck_type_id')
                        ->label('内容类型')
                        ->options(CckType::pluck('display_name', 'id'))
                        ->required()
                        ->default($defaultTypeId)
                        ->live();
                }

                // 标题
                $components[] = Forms\Components\TextInput::make('title')
                    ->label('标题')
                    ->required()
                    ->maxLength(200);

                // 动态字段 - 通过 Schema 闭包响应式渲染
                $components[] = Section::make('内容字段')
                    ->visible(fn (Get $get): bool => (bool) $get('cck_type_id'))
                    ->schema(function (Get $get): array {
                        $typeId = $get('cck_type_id');
                        if (!$typeId) return [];
                        $type = CckType::find($typeId);
                        return $type ? FieldSchemaBuilder::buildForm($type) : [];
                    })
                    ->columnSpanFull();

                // 发布设置
                $components[] = Section::make('发布设置')
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->label('URL 别名')
                            ->maxLength(200)
                            ->helperText('留空自动生成'),
                        Forms\Components\Toggle::make('is_published')
                            ->label('发布')
                            ->default(true),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('发布时间')
                            ->default(now()),
                        Forms\Components\TextInput::make('sort')
                            ->label('排序')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columnSpanFull();

                return $components;
            });
    }

    /**
     * 动态列表：基本列 + 内容类型动态列
     */
    protected static function getTableColumns(): array
    {
        $columns = [
            Tables\Columns\TextColumn::make('title')
                ->label('标题')
                ->searchable()
                ->limit(40),
            Tables\Columns\TextColumn::make('type.display_name')
                ->label('内容类型')
                ->badge()
                ->color(fn($record) => $record->type?->color),
        ];

        // 根据选中的类型添加动态列
        $typeId = request('cck_type_id');
        if ($typeId) {
            $type = CckType::find($typeId);
            if ($type) {
                $dynamicColumns = FieldSchemaBuilder::buildTable($type);
                $columns = array_merge($columns, $dynamicColumns);
            }
        }

        $columns[] = Tables\Columns\TextColumn::make('user.name')
            ->label('作者')
            ->searchable()
            ->toggleable();

        $columns[] = Tables\Columns\IconColumn::make('is_published')
            ->label('发布')
            ->boolean();

        $columns[] = Tables\Columns\TextColumn::make('created_at')
            ->label('创建时间')
            ->dateTime('Y-m-d H:i:s')
            ->width('180px')
            ->sortable();

        return $columns;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('cck_type_id')
                    ->label('内容类型')
                    ->options(CckType::pluck('display_name', 'id')),
            ])
            ->recordUrl(fn($record) => Pages\ViewCckNode::getUrl(['record' => $record]))
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
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

    /**
     * 动态详情：根据记录的内容类型构建显示
     */
    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema(function (?CckNode $record) {
                $entries = [];

                if (! $record) {
                    return $entries;
                }

                // 基本信息
                $entries[] = Section::make('基本信息')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('title')
                            ->label('标题'),
                        \Filament\Infolists\Components\TextEntry::make('type.display_name')
                            ->label('内容类型')
                            ->badge()
                            ->color(fn(CckNode $record) => $record->type?->color),
                        \Filament\Infolists\Components\TextEntry::make('slug')
                            ->label('URL 别名'),
                        \Filament\Infolists\Components\TextEntry::make('user.name')
                            ->label('作者'),
                        \Filament\Infolists\Components\TextEntry::make('is_published')
                            ->label('状态')
                            ->badge()
                            ->formatStateUsing(fn($state) => $state ? '已发布' : '未发布'),
                        \Filament\Infolists\Components\TextEntry::make('published_at')
                            ->label('发布时间')
                            ->dateTime('Y-m-d H:i:s'),
                    ])
                    ->columns(3)
                    ->columnSpanFull();

                // 动态字段
                $typeId = $record->cck_type_id;
                if ($typeId) {
                    $type = CckType::find($typeId);
                    if ($type) {
                        $dynamicEntries = FieldSchemaBuilder::buildInfolist($type);
                        if (!empty($dynamicEntries)) {
                            $entries[] = Section::make('内容字段')
                                ->schema($dynamicEntries)
                                ->columnSpanFull();
                        }
                    }
                }

                // 时间信息
                $entries[] = Section::make('时间信息')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('created_at')
                            ->label('创建时间')
                            ->dateTime('Y-m-d H:i:s'),
                        \Filament\Infolists\Components\TextEntry::make('updated_at')
                            ->label('更新时间')
                            ->dateTime('Y-m-d H:i:s'),
                    ])
                    ->columns(2)
                    ->columnSpanFull();

                return $entries;
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCckNodes::route('/'),
            'create' => Pages\CreateCckNode::route('/create'),
            'view' => Pages\ViewCckNode::route('/{record}'),
            'edit' => Pages\EditCckNode::route('/{record}/edit'),
        ];
    }
}
