<?php

namespace App\Filament\Resources\Planos\Tables;

use App\Models\Plano;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column as ExcelColumn;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use App\Enums\PlanStatus;

class PlanosTable
{
    /** Rótulos da periodicidade */
    private const PERIOD_LABELS = [
        'MENSAL'     => 'Mensal',
        'TRIMESTRAL' => 'Trimestral',
        'SEMESTRAL'  => 'Semestral',
        'ANUAL'      => 'Anual',
    ];

    /* ======================== Helpers de normalização ======================== */

    /** Desembrulha enums e pega o valor cru do DB quando necessário */
    private static function raw(mixed $v): mixed
    {
        if ($v instanceof BackedEnum) {
            return $v->value;
        }
        return $v;
    }

    /** Normaliza valores “verdadeiros” (1, '1', true, 'true', 'sim') */
    private static function truthy(mixed $v): bool
    {
        $v = self::raw($v);

        if (is_bool($v)) return $v;
        if (is_numeric($v)) return (int) $v === 1;
        if (is_string($v)) {
            $v = trim(mb_strtolower($v));
            return in_array($v, ['1', 'true', 't', 'sim', 's', 'y', 'yes', 'ativo', 'active'], true);
        }
        return false;
    }

    /** É status ativo independentemente do formato salvo */
    private static function isActive(mixed $status): bool
    {
        $status = self::raw($status);

        if (is_string($status)) {
            $s = mb_strtoupper(trim($status));
            return in_array($s, ['ATIVO', 'ACTIVE', 'A'], true);
        }
        return self::truthy($status);
    }

    /** Retorna a string padronizada (ATIVO | INATIVO) */
    private static function normalizedStatus(mixed $status): string
    {
        return self::isActive($status) ? 'ATIVO' : 'INATIVO';
    }

    private static function periodLabel(?string $state): ?string
    {
        if ($state === null || $state === '') return null;
        $key = strtoupper((string) $state);
        return self::PERIOD_LABELS[$key] ?? $state;
    }

    private static function periodColor(?string $state): string
    {
        return match (strtoupper((string) $state)) {
            'MENSAL'     => 'primary',
            'TRIMESTRAL' => 'info',
            'SEMESTRAL'  => 'warning',
            'ANUAL'      => 'success',
            default      => 'gray',
        };
    }

    private static function money($v): string
    {
        // Considera null, string vazia, '0', 0, 0.0, '0.00' como zero
        if ($v === null || $v === '' || $v === false) {
            $n = 0.0;
        } else if (is_numeric($v)) {
            $n = (float) $v;
        } else {
            $n = 0.0;
        }
        return 'R$ ' . number_format($n, 2, ',', '.');
    }

    /** Gera um nome único para a duplicação */
    private static function nextCopyName(string $base): string
    {
        $name = $base . ' (cópia)';
        $i = 2;
        while (Plano::where('nome', $name)->exists()) {
            $name = $base . " (cópia {$i})";
            $i++;
        }
        return $name;
    }

    public static function make(Table $table): Table
    {
        return $table
            ->reorderable('ordem')
            ->defaultSort('ordem')
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('periodicidade')
                    ->label('Periodicidade')
                    ->state(fn ($record) =>
                        ($p = data_get($record, 'periodicidade')) instanceof \App\Enums\PlanPeriod
                            ? $p
                            : (is_string($p) ? \App\Enums\PlanPeriod::tryFrom(strtoupper($p)) ?? $p : $p)
                    )
                    ->formatStateUsing(fn ($state) =>
                        $state instanceof \App\Enums\PlanPeriod
                            ? $state->label()
                            : (is_string($state) ? self::periodLabel($state) : '—')
                    )
                    ->badge()
                    ->color(fn ($state) =>
                        $state instanceof \App\Enums\PlanPeriod
                            ? $state->color()
                            : (is_string($state) ? self::periodColor($state) : 'gray')
                    )
                    ->sortable(),

                TextColumn::make('valor')
                    ->label('Valor')
                    ->alignRight()
                    ->formatStateUsing(fn ($_, $record) => self::money($record->valor))
                    ->extraAttributes(function ($record) {
                        return ($record->valor !== null && (float) $record->valor == 0.0)
                            ? ['class' => 'font-medium']
                            : [];
                    })
                    ->sortable(),

                // STATUS: verde ATIVO, vermelho INATIVO — com suporte a Enum/booleans
                TextColumn::make('status')
                    ->label('Status')
                    ->state(function ($record) {
                        // $record->status já é PlanStatus por causa do cast
                        return $record->status instanceof PlanStatus
                            ? $record->status->label()
                            : (mb_strtoupper((string) $record->status) === 'ATIVO' ? 'ATIVO' : 'INATIVO');
                    })
                    ->badge()
                    ->color(function ($record) {
                        return $record->status instanceof PlanStatus
                            ? $record->status->color()
                            : (mb_strtoupper((string) $record->status) === 'ATIVO' ? 'success' : 'danger');
                    })
                    ->alignCenter()
                    ->sortable(),

                // OCULTO: Sim/Não com cores (Sim = danger, Não = success)
                TextColumn::make('oculto')
                    ->label('Oculto')
                    ->state(fn (Plano $r) => $r->oculto ? 'Sim' : 'Não')
                    ->badge()
                    ->color(fn (Plano $r) => $r->oculto ? 'danger' : 'success')
                    ->alignCenter(),

                TextColumn::make('clientes_count')
                    ->counts('clientes')
                    ->label('Clientes vinculados')
                    ->sortable()
                    ->description('vinculados')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('periodicidade')
                    ->label('Periodicidade')
                    ->options(self::PERIOD_LABELS),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options(['ATIVO' => 'Ativo', 'INATIVO' => 'Inativo'])
                    ->indicator('Status')
                    ->native(false),

                TernaryFilter::make('oculto')->label('Oculto'),

                Filter::make('valor')
                    ->label('Valor (faixa)')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('min')->label('Valor mínimo')->numeric()->prefix('R$'),
                        \Filament\Forms\Components\TextInput::make('max')->label('Valor máximo')->numeric()->prefix('R$'),
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['min'] !== null && $data['min'] !== '') {
                            $query->where('valor', '>=', $data['min']);
                        }
                        if ($data['max'] !== null && $data['max'] !== '') {
                            $query->where('valor', '<=', $data['max']);
                        }
                    }),
            ])
            ->actions([
                ViewAction::make('detalhes')
                    ->label('Detalhes')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->tooltip('Visualizar detalhes do plano')
                    ->modalWidth('4xl')
                    ->modalHeading(fn ($record) => 'Detalhes do plano: ' . ($record->nome ?? '—'))
                    ->modalContent(function ($record) {
                        $record->loadCount('clientes');
                        $periodLabels = self::PERIOD_LABELS;
                        $money = fn ($v) => self::money($v);
                        return view('filament.planos._detalhes', [
                            'p'            => $record,
                            'periodLabels' => $periodLabels,
                            'money'        => $money,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar'),

            // Alternar ATIVO/INATIVO (rótulo: "Ativo" quando inativo; "Inativar" quando ativo)
            Action::make('alternarStatus')
                ->label(fn (?Plano $r) => ($r && $r->is_active) ? 'Inativar' : 'Ativar')
                ->color(fn (?Plano $r) => ($r && $r->is_active) ? 'danger' : 'success')
                ->icon('heroicon-o-power')
                ->tooltip(fn (?Plano $r) => ($r && $r->is_active) ? 'Inativar plano' : 'Ativar plano')
                ->requiresConfirmation()
                ->action(function (Plano $record) {
                    $toActive = ! $record->is_active;
                    $record->update([
                        'status' => $toActive ? \App\Enums\PlanStatus::ATIVO : \App\Enums\PlanStatus::INATIVO,
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title($toActive ? 'Plano ativado com sucesso.' : 'Plano inativado com sucesso.')
                        ->success()
                        ->send();
                }),

                // Alternar oculto/exibir
                Action::make('alternarOculto')
                    // rótulo: se está oculto -> Exibir; se está visível -> Ocultar
                    ->label(fn (?Plano $r) => ($r && $r->oculto) ? 'Exibir' : 'Ocultar')
                    ->icon(fn (?Plano $r) => ($r && $r->oculto) ? 'heroicon-o-eye' : 'heroicon-o-eye-slash')
                    ->color(fn (?Plano $r) => ($r && $r->oculto) ? 'success' : 'warning')
                    ->tooltip(fn (?Plano $r) => ($r && $r->oculto) ? 'Exibir na vitrine' : 'Ocultar da vitrine')

                    ->requiresConfirmation()
                    ->modalHeading(fn (?Plano $r) => ($r && $r->oculto) ? 'Exibir plano' : 'Ocultar plano')
                    ->modalDescription(fn (?Plano $r) => ($r && $r->oculto)
                        ? 'O plano será exibido novamente nas listagens e pode aparecer em vitrines públicas e integrações.'
                        : 'O plano será ocultado das listagens públicas e catálogos. Ele pode deixar de aparecer em vitrines e integrações.'
                    )

                    ->action(function (Plano $record, Action $action) {
                        // calcula o novo valor antes de salvar
                        $novoOculto = ! (bool) $record->oculto;

                        $record->update(['oculto' => $novoOculto]);

                        \Filament\Notifications\Notification::make()
                            ->title($novoOculto ? 'Plano ocultado.' : 'Plano exibido.')
                            ->success()
                            ->send();

                        // força o re-render da tabela
                        $action->getLivewire()->dispatch('refresh');
                    }),

                EditAction::make()
                    ->label('Editar')
                    ->tooltip('Editar plano'),

                DeleteAction::make()
                    ->label('Excluir')
                    ->disabled(fn ($record) => $record->clientes()->exists())
                    ->tooltip('Só é possível excluir planos sem clientes'),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make('novo')
                    ->label('Novo plano')
                    ->icon('heroicon-o-plus')
                    ->color('success'),

                ExportAction::make('exportar')
                    ->label('Exportar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exports([
                        ExcelExport::make('Planos')
                            ->fromTable()
                            ->withColumns([
                                ExcelColumn::make('nome')->heading('Nome'),
                                ExcelColumn::make('periodicidade')->heading('Periodicidade')->formatStateUsing(fn ($s) => self::periodLabel($s) ?? '—'),
                                ExcelColumn::make('valor')->heading('Valor')->formatStateUsing(fn ($v) => self::money($v)),
                                ExcelColumn::make('taxa_adesao')->heading('Taxa Adesão')->formatStateUsing(fn ($v) => self::money($v)),
                                ExcelColumn::make('trial_dias')->heading('Trial (dias)'),
                                ExcelColumn::make('status')->heading('Status')->formatStateUsing(fn ($s) => self::normalizedStatus($s)),
                                ExcelColumn::make('oculto')->heading('Oculto')->formatStateUsing(fn ($v) => self::truthy($v) ? 'Sim' : 'Não'),
                                ExcelColumn::make('descricao')->heading('Descrição'),
                                ExcelColumn::make('recursos')->heading('Recursos')->formatStateUsing(fn ($v) => is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v),
                                ExcelColumn::make('limites')->heading('Limites')->formatStateUsing(fn ($v) => is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v),
                            ])
                            ->withFilename('planos-' . now()->format('d-m-Y'))
                            ->withWriterType(Excel::XLSX),
                    ]),
            ])
            ->bulkActions([
                ExportBulkAction::make('exportarBulk')
                    ->label('Exportar')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->exports([
                        ExcelExport::make('Planos selecionados')
                            ->fromTable()
                            ->withColumns([
                                ExcelColumn::make('nome')->heading('Nome'),
                                ExcelColumn::make('periodicidade')->heading('Periodicidade')->formatStateUsing(fn ($s) => self::periodLabel($s) ?? '—'),
                                ExcelColumn::make('valor')->heading('Valor')->formatStateUsing(fn ($v) => self::money($v)),
                                ExcelColumn::make('taxa_adesao')->heading('Taxa Adesão')->formatStateUsing(fn ($v) => self::money($v)),
                                ExcelColumn::make('trial_dias')->heading('Trial (dias)'),
                                ExcelColumn::make('status')->heading('Status')->formatStateUsing(fn ($s) => self::normalizedStatus($s)),
                                ExcelColumn::make('oculto')->heading('Oculto')->formatStateUsing(fn ($v) => self::truthy($v) ? 'Sim' : 'Não'),
                                ExcelColumn::make('descricao')->heading('Descrição'),
                                ExcelColumn::make('recursos')->heading('Recursos')->formatStateUsing(fn ($v) => is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v),
                                ExcelColumn::make('limites')->heading('Limites')->formatStateUsing(fn ($v) => is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v),
                            ])
                            ->withFilename('planos-' . now()->format('d-m-Y'))
                            ->withWriterType(Excel::XLSX),
                    ]),

                BulkActionGroup::make([
                    BulkAction::make('duplicar')
                        ->label('Duplicar')
                        ->icon('heroicon-o-document-duplicate')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records) {
                            $nextOrder = (int) (Plano::max('ordem') ?? 0);
                            $created = 0;

                            foreach ($records as $record) {
                                /** @var \App\Models\Plano $record */
                                $copy = $record->replicate([
                                    'slug', 'ordem', 'created_at', 'updated_at', 'deleted_at',
                                ]);

                                $copy->nome  = self::nextCopyName($record->nome);
                                $copy->slug  = null;
                                $copy->ordem = ++$nextOrder;
                                unset($copy->clientes_count);

                                $copy->save();
                                $created++;
                            }

                            Notification::make()
                                ->title($created > 1 ? "{$created} planos duplicados com sucesso!" : "Plano duplicado com sucesso!")
                                ->success()
                                ->send();
                        }),

                    DeleteBulkAction::make()
                        ->disabled(fn (Collection $records) =>
                            $records->first(fn ($r) => $r->clientes()->exists()) !== null
                        )
                        ->tooltip('Só é possível excluir planos sem clientes'),
                ]),

                    BulkAction::make('ativarEmMassa')
                        ->label('Ativar')
                        ->icon('heroicon-o-power')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $ok = 0;
                            foreach ($records as $p) {
                                if (! $p->is_active) {
                                    $p->update(['status' => PlanStatus::ATIVO]);
                                    $ok++;
                                }
                            }
                            \Filament\Notifications\Notification::make()
                                ->title($ok ? "{$ok} plano(s) ativado(s)." : 'Nenhum plano precisou ser ativado.')
                                ->success()->send();
                        }),

                    BulkAction::make('inativarEmMassa')
                        ->label('Inativar')
                        ->icon('heroicon-o-power')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $ok = 0;
                            foreach ($records as $p) {
                                if ($p->is_active) {
                                    $p->update(['status' => PlanStatus::INATIVO]);
                                    $ok++;
                                }
                            }
                            \Filament\Notifications\Notification::make()
                                ->title($ok ? "{$ok} plano(s) inativado(s)." : 'Nenhum plano precisou ser inativado.')
                                ->success()->send();
                        }),

                    BulkAction::make('ocultarEmMassa')
                        ->label('Ocultar')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $ok = 0;
                            foreach ($records as $p) {
                                if (! $p->oculto) {
                                    $p->update(['oculto' => true]);
                                    $ok++;
                                }
                            }
                            \Filament\Notifications\Notification::make()
                                ->title($ok ? "{$ok} plano(s) ocultado(s)." : 'Nada a ocultar.')
                                ->success()->send();
                        }),

                    BulkAction::make('exibirEmMassa')
                        ->label('Exibir')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $ok = 0;
                            foreach ($records as $p) {
                                if ($p->oculto) {
                                    $p->update(['oculto' => false]);
                                    $ok++;
                                }
                            }
                            \Filament\Notifications\Notification::make()
                                ->title($ok ? "{$ok} plano(s) exibido(s)." : 'Nada a exibir.')
                                ->success()->send();
                        }),
            ]);
    }
}
