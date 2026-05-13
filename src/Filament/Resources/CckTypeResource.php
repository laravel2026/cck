<?php

namespace Modules\Cck\Filament\Resources;

use Modules\Cck\Models\CckType;
use Modules\Cck\Filament\Resources\CckTypeResource\Pages;
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
use Modules\Cck\Lib\FieldTypeRegistry;

class CckTypeResource extends Resource
{
    protected static ?string $model = CckType::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = '内容类型';

    protected static ?string $modelLabel = '内容类型';

    protected static \UnitEnum|string|null $navigationGroup = 'CCK 内容管理';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Forms\Components\TextInput::make('display_name')
                    ->label('显示名')
                    ->required()
                    ->maxLength(200)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($set, $state) {
                        $set('name', \Illuminate\Support\Str::snake($state));
                    }),
                Forms\Components\TextInput::make('name')
                    ->label('机器名')
                    ->required()
                    ->maxLength(100)
                    ->helperText('英文小写，如 article'),
                Forms\Components\Textarea::make('description')
                    ->label('描述')
                    ->rows(3),
                Forms\Components\Toggle::make('is_active')
                    ->label('启用')
                    ->default(true),
                Forms\Components\TextInput::make('sort')
                    ->label('排序')
                    ->numeric()
                    ->default(0),

                // API 权限
                \Filament\Schemas\Components\Section::make('API 权限')
                    ->schema([
                        Forms\Components\Toggle::make('api_auth_required')
                            ->label('接口需登录')
                            ->helperText('开启后，所有 CRUD 接口（列表/详情/创建/更新/删除）均需登录才能访问'),
                        Forms\Components\Toggle::make('api_own_only')
                            ->label('仅操作自己的内容')
                            ->helperText('开启后，列表只返回自己的，增删改也校验归属'),
                    ])
                    ->columnSpanFull(),

                // 字段管理
                \Filament\Schemas\Components\Section::make('字段定义')
                    ->schema([
                        Forms\Components\Repeater::make('fields')
                            ->hiddenLabel()
                            ->relationship()
                            ->addActionLabel('添加字段')
                            ->reorderable('sort_order')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('机器名')
                                    ->required()
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('display_name')
                                    ->label('显示名')
                                    ->required()
                                    ->maxLength(200),
                                Forms\Components\Select::make('field_type')
                                    ->label('字段类型')
                                    ->options(FieldTypeRegistry::options())
                                    ->required()
                                    ->live(),
                                Forms\Components\Toggle::make('is_required')
                                    ->label('必填'),
                                Forms\Components\Toggle::make('show_in_form')
                                    ->label('表单显示')
                                    ->default(true),
                                Forms\Components\Toggle::make('show_in_list')
                                    ->label('列表显示')
                                    ->default(true),
                                Forms\Components\Toggle::make('show_in_detail')
                                    ->label('详情显示')
                                    ->default(true),
                                Forms\Components\TextInput::make('list_width')
                                    ->label('列表列宽')
                                    ->placeholder('如 200px'),
                                Forms\Components\Toggle::make('list_sortable')
                                    ->label('列表可排序'),
                                Forms\Components\TextInput::make('sort_order')
                                    ->label('排序')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(fn(array $state): string => $state['display_name'] ?? '新字段'),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label('显示名')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('机器名')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('启用')
                    ->boolean(),
                Tables\Columns\TextColumn::make('fields_count')
                    ->label('字段数')
                    ->counts('fields'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('作者')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新时间')
                    ->dateTime('Y-m-d H:i:s')
                    ->width('180px')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
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
            'index' => Pages\ListCckTypes::route('/'),
            'create' => Pages\CreateCckType::route('/create'),
            'edit' => Pages\EditCckType::route('/{record}/edit'),
        ];
    }
}
