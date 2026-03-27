<?php

namespace Webkul\Employee\Filament\Clusters\Configurations\Resources;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Webkul\Employee\Filament\Clusters\Configurations;
use Webkul\Employee\Filament\Clusters\Configurations\Resources\SkillDisciplineResource\Pages\ManageSkillDisciplines;
use Webkul\Employee\Models\SkillDiscipline;

class SkillDisciplineResource extends Resource
{
    protected static ?string $model = SkillDiscipline::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 3;

    protected static ?string $cluster = Configurations::class;

    public static function getNavigationLabel(): string
    {
        return 'Skill Disciplines';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->maxLength(255),
                Select::make('skill_domain_id')
                    ->relationship('domain', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Toggle::make('is_active')->default(true),
                Textarea::make('description')->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('domain.name')->label('Domain')->sortable(),
                IconColumn::make('is_active')->boolean()->sortable(),
                TextColumn::make('creator.name')->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSkillDisciplines::route('/'),
        ];
    }
}
