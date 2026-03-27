<?php

namespace Webkul\Project\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Webkul\Employee\Models\Skill;
use Webkul\Employee\Models\SkillDiscipline;
use Webkul\Employee\Models\SkillDomain;

class RequiredSkillsRelationManager extends RelationManager
{
    protected static string $relationship = 'requiredSkills';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('skill_domain_id')
                    ->label('Domain')
                    ->options(SkillDomain::query()->pluck('name', 'id'))
                    ->live()
                    ->required()
                    ->afterStateUpdated(fn (callable $set) => $set('skill_discipline_id', null)),
                Select::make('skill_discipline_id')
                    ->label('Discipline')
                    ->options(
                        fn (callable $get) => SkillDiscipline::query()
                            ->where('skill_domain_id', $get('skill_domain_id'))
                            ->pluck('name', 'id')
                    )
                    ->live()
                    ->required()
                    ->afterStateUpdated(fn (callable $set) => $set('skill_id', null)),
                Select::make('skill_id')
                    ->label('Skill')
                    ->options(
                        fn (callable $get) => Skill::query()
                            ->when($get('skill_discipline_id'), fn ($query, $disciplineId) => $query->where('skill_discipline_id', $disciplineId))
                            ->pluck('name', 'id')
                    )
                    ->required()
                    ->searchable(),
                Select::make('proficiency')
                    ->options([
                        'basic'        => 'Basic',
                        'intermediate' => 'Intermediate',
                        'advanced'     => 'Advanced',
                        'expert'       => 'Expert',
                    ])
                    ->default('basic')
                    ->required(),
                TextInput::make('required_fte_percent')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required(),
                TextInput::make('required_person_months')
                    ->numeric()
                    ->minValue(0)
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('domain.name')
                    ->label('Domain')
                    ->sortable(),
                TextColumn::make('discipline.name')
                    ->label('Discipline')
                    ->sortable(),
                TextColumn::make('skill.name')
                    ->label('Skill')
                    ->sortable(),
                TextColumn::make('proficiency')
                    ->badge(),
                TextColumn::make('required_fte_percent')
                    ->numeric(2)
                    ->suffix('%')
                    ->label('Required FTE')
                    ->sortable(),
                TextColumn::make('required_person_months')
                    ->numeric(4)
                    ->label('Required PM')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()->icon('heroicon-o-plus-circle'),
            ]);
    }
}
