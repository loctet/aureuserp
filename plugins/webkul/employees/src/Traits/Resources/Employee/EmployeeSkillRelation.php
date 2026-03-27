<?php

namespace Webkul\Employee\Traits\Resources\Employee;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Webkul\Employee\Models\Employee;
use Webkul\Employee\Models\EmployeeSkill;
use Webkul\Employee\Models\Skill;
use Webkul\Employee\Models\SkillDiscipline;
use Webkul\Employee\Models\SkillDomain;
use Webkul\Employee\Models\SkillType;
use Webkul\Support\Filament\Tables as CustomTables;

trait EmployeeSkillRelation
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make([
                    Hidden::make('creator_id')
                        ->default(fn () => Auth::user()->id),
                    Select::make('skill_domain_id')
                        ->label('Domain')
                        ->options(SkillDomain::query()->pluck('name', 'id'))
                        ->required()
                        ->live()
                        ->dehydrated(false)
                        ->afterStateUpdated(fn (callable $set) => $set('skill_discipline_id', null)),
                    Select::make('skill_discipline_id')
                        ->label('Discipline')
                        ->options(
                            fn (callable $get) => SkillDiscipline::query()
                                ->where('skill_domain_id', $get('skill_domain_id'))
                                ->pluck('name', 'id')
                        )
                        ->required()
                        ->live()
                        ->dehydrated(false)
                        ->afterStateUpdated(fn (callable $set) => $set('skill_id', null)),
                    Select::make('skill_type_id')
                        ->label(__('employees::filament/resources/employee/relation-manager/skill.form.sections.fields.skill-type'))
                        ->options(SkillType::pluck('name', 'id'))
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (callable $set) => $set('skill_level_id', null)),
                    Group::make()
                        ->schema([
                            Select::make('skill_id')
                                ->label(__('employees::filament/resources/employee/relation-manager/skill.form.sections.fields.skill'))
                                ->options(
                                    fn (callable $get) => Skill::query()
                                        ->where('skill_type_id', $get('skill_type_id'))
                                        ->when($get('skill_discipline_id'), fn ($query, $disciplineId) => $query->where('skill_discipline_id', $disciplineId))
                                        ->pluck('name', 'id')
                                )
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (callable $set, $state): void {
                                    $skill = Skill::find($state);
                                    $set('skill_type_id', $skill?->skill_type_id);
                                    $set('skill_level_id', null);
                                }),
                            Select::make('skill_level_id')
                                ->label(__('employees::filament/resources/employee/relation-manager/skill.form.sections.fields.skill-level'))
                                ->options(
                                    fn (callable $get) => SkillType::find($get('skill_type_id'))?->skillLevels->pluck('name', 'id') ?? []
                                )
                                ->required(),
                            Select::make('proficiency')
                                ->options([
                                    'basic'        => 'Basic',
                                    'intermediate' => 'Intermediate',
                                    'advanced'     => 'Advanced',
                                    'expert'       => 'Expert',
                                ])
                                ->default(EmployeeSkill::PROFICIENCY_BASIC)
                                ->required(),
                            Select::make('validation_status')
                                ->options([
                                    'pending'   => 'Pending',
                                    'validated' => 'Validated',
                                    'rejected'  => 'Rejected',
                                ])
                                ->default(EmployeeSkill::VALIDATION_PENDING)
                                ->required(),
                            Select::make('validated_by')
                                ->label('Validated By')
                                ->options(function (): array {
                                    $owner = method_exists($this, 'getOwnerRecord') ? $this->getOwnerRecord() : null;
                                    $managerId = $owner?->department?->manager_id;

                                    if ($managerId) {
                                        return Employee::query()
                                            ->whereKey($managerId)
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    }

                                    return Employee::query()->pluck('name', 'id')->toArray();
                                })
                                ->searchable()
                                ->helperText('Defaults to the employee area manager when assigned.'),
                            Textarea::make('validation_notes')
                                ->label('Validation Notes')
                                ->rows(3),
                        ]),
                ])->columns(2)->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('skillType.name')
                    ->label(__('employees::filament/resources/employee/relation-manager/skill.table.columns.skill-type'))
                    ->sortable(),
                TextColumn::make('skill.name')
                    ->label(__('employees::filament/resources/employee/relation-manager/skill.table.columns.skill'))
                    ->sortable(),
                TextColumn::make('proficiency')
                    ->badge(),
                TextColumn::make('skillLevel.name')
                    ->label(__('employees::filament/resources/employee/relation-manager/skill.table.columns.skill-level'))
                    ->badge()
                    ->color(fn ($record) => $record->skillType?->color),
                TextColumn::make('validation_status')
                    ->badge(),
                TextColumn::make('validatedBy.name')
                    ->label('Validated By')
                    ->sortable(),
                CustomTables\Columns\ProgressBarEntry::make('skillLevel.level')
                    ->getStateUsing(fn ($record) => $record->skillLevel?->level)
                    ->color(function ($record) {
                        if ($record->skillLevel?->level === 100) {
                            return 'success';
                        } elseif ($record->skillLevel?->level >= 50 && $record->skillLevel?->level < 80) {
                            return 'warning';
                        } elseif ($record->skillLevel?->level < 20) {
                            return 'danger';
                        } else {
                            return 'info';
                        }
                    })
                    ->label(__('employees::filament/resources/employee/relation-manager/skill.table.columns.level-percent')),
                TextColumn::make('creator.name')
                    ->label(__('employees::filament/resources/employee/relation-manager/skill.table.columns.created-by'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('employees::filament/resources/employee/relation-manager/skill.table.columns.created-at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->date(),
            ])
            ->groups([
                Tables\Grouping\Group::make('skillType.name')
                    ->label(__('employees::filament/resources/employee/relation-manager/skill.table.groups.skill-type'))
                    ->collapsible(),
            ])
            ->filters([])
            ->headerActions([
                CreateAction::make()
                    ->label(__('employees::filament/resources/employee/relation-manager/skill.table.header-actions.add-skill'))
                    ->icon('heroicon-o-plus-circle')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('employees::filament/resources/employee/relation-manager/skill.table.actions.create.notification.title'))
                            ->body(__('employees::filament/resources/employee/relation-manager/skill.table.actions.create.notification.body'))
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('employees::filament/resources/employee/relation-manager/skill.table.actions.edit.notification.title'))
                            ->body(__('employees::filament/resources/employee/relation-manager/skill.table.actions.edit.notification.body'))
                    ),
                DeleteAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title(__('employees::filament/resources/employee/relation-manager/skill.table.actions.delete.notification.title'))
                            ->body(__('employees::filament/resources/employee/relation-manager/skill.table.actions.delete.notification.body'))
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title(__('employees::filament/resources/employee/relation-manager/skill.table.bulk-actions.delete.notification.title'))
                                ->body(__('employees::filament/resources/employee/relation-manager/skill.table.bulk-actions.delete.notification.body'))
                        ),
                ]),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Group::make()
                            ->schema([
                                TextEntry::make('skillType.name')
                                    ->placeholder('—')
                                    ->label(__('employees::filament/resources/employee/relation-manager/skill.infolist.entries.skill-type')),
                                TextEntry::make('skill.name')
                                    ->placeholder('—')
                                    ->label(__('employees::filament/resources/employee/relation-manager/skill.infolist.entries.skill')),
                                TextEntry::make('skillLevel.name')
                                    ->placeholder('—')
                                    ->badge()
                                    ->color(fn ($record) => $record->skillType?->color)
                                    ->label(__('employees::filament/resources/employee/relation-manager/skill.infolist.entries.skill-level')),
                                CustomTables\Infolists\ProgressBarEntry::make('skillLevel.level')
                                    ->getStateUsing(fn ($record) => $record->skillLevel?->level)
                                    ->color(function ($record) {
                                        if ($record->skillLevel->level === 100) {
                                            return 'success';
                                        } elseif ($record->skillLevel->level >= 50 && $record->skillLevel->level < 80) {
                                            return 'warning';
                                        } elseif ($record->skillLevel->level < 20) {
                                            return 'danger';
                                        } else {
                                            return 'info';
                                        }
                                    })
                                    ->label(__('employees::filament/resources/employee/relation-manager/skill.infolist.entries.level-percent')),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan('full'),
            ]);
    }
}
