{{-- resources/views/admin/sanctions/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Sanctions')
@section('page-title', 'Sanctions disciplinaires')
@section('page-sub', 'Historique et gestion des mesures disciplinaires')

@section('topbar-actions')
    <select class="form-control" style="width:auto"
        onchange="window.location='?annee='+this.value+'&statut={{ request('statut') }}'">
        @for ($y = now()->year; $y >= now()->year - 3; $y--)
            <option value="{{ $y }}" {{ ($annee ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}
            </option>
        @endfor
    </select>
    <button class="btn btn-primary" onclick="document.getElementById('modal-sanction').style.display='flex'">
        + Prononcer une sanction
    </button>
@endsection

@section('content')

    {{-- Stats --}}
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px">
        <div class="stat-card">
            <div class="stat-label">Total sanctions</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-sub">Année {{ $annee }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">En cours</div>
            <div class="stat-value" style="color:var(--crimson)">{{ $stats['en_cours'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Avertissements</div>
            <div class="stat-value" style="color:var(--amber)">{{ $stats['avertissements'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Mises à pied</div>
            <div class="stat-value" style="color:var(--crimson)">{{ $stats['mises_a_pied'] }}</div>
        </div>
    </div>

    {{-- Filtres --}}
    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
        @foreach (['' => 'Tous les statuts', 'en_cours' => 'En cours', 'executee' => 'Exécutée', 'levee' => 'Levée'] as $val => $label)
            <a href="?annee={{ $annee }}&statut={{ $val }}&type={{ request('type') }}"
                class="btn btn-sm {{ request('statut') === $val || (request('statut') === null && $val === '') ? 'btn-primary' : '' }}">
                {{ $label }}
            </a>
        @endforeach
        <div style="margin-left:auto">
            <select class="form-control" style="width:auto"
                onchange="window.location='?annee={{ $annee }}&statut={{ request('statut') }}&type='+this.value">
                <option value="">Tous les types</option>
                @foreach (['avertissement_verbal' => 'Avertissement verbal', 'avertissement_ecrit' => 'Avertissement écrit', 'mise_a_pied' => 'Mise à pied', 'retenue_salaire' => 'Retenue salaire', 'licenciement' => 'Licenciement'] as $val => $label)
                    <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>
                        {{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Employé</th>
                        <th>Type</th>
                        <th>Motif</th>
                        <th>Date</th>
                        <th>Durée</th>
                        <th>Retenue ($)</th>
                        <th>Signé</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sanctions as $s)
                        <tr>
                            <td>
                                <div class="emp-cell">
                                    <div class="avatar">{{ $s->employe->initiales }}</div>
                                    <div>
                                        <div class="emp-name">{{ $s->employe->nom }} {{ $s->employe->prenom }}</div>
                                        <div class="emp-sub">{{ $s->employe->fonction }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span
                                    class="badge {{ in_array($s->type, ['avertissement_verbal', 'avertissement_ecrit']) ? 'badge-amber' : 'badge-red' }}">
                                    {{ $s->type_libelle }}
                                </span>
                            </td>
                            <td class="text-sm" style="max-width:200px;white-space:normal">{{ $s->motif }}</td>
                            <td class="font-mono text-sm">{{ $s->date_debut->format('d/m/Y') }}</td>
                            <td class="text-sm">
                                @if ($s->duree_jours)
                                    {{ $s->duree_jours }} jour(s)
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="font-mono text-sm"
                                style="color:{{ $s->montant_retenu > 0 ? 'var(--crimson)' : 'var(--ink3)' }}">
                                {{ $s->montant_retenu > 0 ? '- ' . number_format($s->montant_retenu, 2, ',', ' ') . ' $' : '—' }}
                            </td>
                            <td style="text-align:center">
                                @if ($s->signe_employe)
                                    <span title="Signé le {{ $s->date_signature?->format('d/m/Y') }}">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                            stroke="var(--emerald)" stroke-width="2.2">
                                            <circle cx="8" cy="8" r="6.5" />
                                            <path d="M5 8l2 2 4-4" />
                                        </svg>
                                    </span>
                                @else
                                    <span class="text-muted text-xs">Non signé</span>
                                @endif
                            </td>
                            <td>
                                @if ($s->statut === 'en_cours')
                                    <span class="badge badge-red">En cours</span>
                                @elseif($s->statut === 'executee')
                                    <span class="badge badge-green">Exécutée</span>
                                @else
                                    <span class="badge badge-gray">Levée</span>
                                @endif
                            </td>
                            <td>
                                <div class="table-actions">
                                    @if ($s->statut === 'en_cours')
                                        @if (!$s->signe_employe)
                                            <form method="POST" action="{{ route('admin.sanctions.signer', $s) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-emerald"
                                                    title="Marquer comme signé">✓</button>
                                            </form>
                                        @endif
                                        <button class="btn btn-sm" onclick="leverSanction({{ $s->id }})"
                                            title="Lever la sanction">↩</button>
                                    @endif
                                    <form method="POST" action="{{ route('admin.sanctions.destroy', $s) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Archiver cette sanction ?')"
                                            title="Archiver">🗑</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty">
                                    <div class="empty-icon">⚖️</div>
                                    <div class="empty-title">Aucune sanction enregistrée</div>
                                    <div class="empty-sub">Les sanctions disciplinaires apparaîtront ici.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($sanctions->hasPages())
            <div class="card-footer" style="display:flex;justify-content:flex-end">{{ $sanctions->links() }}</div>
        @endif
    </div>

    {{-- Modal : nouvelle sanction --}}
    <div class="modal-overlay" id="modal-sanction" style="display:none">
        <div class="modal">
            <div class="modal-header">
                <span class="modal-title">Prononcer une sanction disciplinaire</span>
                <button class="modal-close"
                    onclick="document.getElementById('modal-sanction').style.display='none'">✕</button>
            </div>
            <form method="POST" action="{{ route('admin.sanctions.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning mb-4">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <path d="M8 2L1 13h14L8 2z" />
                            <path d="M8 7v3M8 11.5h.01" />
                        </svg>
                        Une sanction est une mesure officielle et irréversible. Vérifiez les faits avant de valider.
                    </div>

                    <div class="form-group">
                        <label class="form-label">Employé <span class="required">*</span></label>
                        <select name="employe_id" class="form-control" required>
                            <option value="">— Sélectionner —</option>
                            @foreach ($employes as $e)
                                <option value="{{ $e->id }}">{{ $e->nom }} {{ $e->prenom }} —
                                    {{ $e->fonction }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row form-row-2">
                        <div class="form-group">
                            <label class="form-label">Type de sanction <span class="required">*</span></label>
                            <select name="type" class="form-control" required id="sanction-type"
                                onchange="toggleMontant(this.value)">
                                <option value="avertissement_verbal">Avertissement verbal</option>
                                <option value="avertissement_ecrit">Avertissement écrit</option>
                                <option value="mise_a_pied">Mise à pied</option>
                                <option value="retenue_salaire">Retenue sur salaire</option>
                                <option value="licenciement">Licenciement</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date de début <span class="required">*</span></label>
                            <input type="date" name="date_debut" class="form-control"
                                value="{{ now()->toDateString() }}" required>
                        </div>
                    </div>

                    <div class="form-row form-row-2" id="zone-fin-montant">
                        <div class="form-group">
                            <label class="form-label">Date de fin <small class="text-muted">(mise à pied)</small></label>
                            <input type="date" name="date_fin" class="form-control">
                        </div>
                        <div class="form-group" id="zone-montant">
                            <label class="form-label">Montant retenu ($) <small
                                    class="text-muted">(retenue)</small></label>
                            <input type="number" name="montant_retenu" class="form-control" min="0"
                                step="0.01" value="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Motif <span class="required">*</span></label>
                        <input type="text" name="motif" class="form-control" required
                            placeholder="Ex : 3 absences non justifiées consécutives">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description détaillée</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Circonstances, décisions prises…"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Document officiel (PV, lettre…)</label>
                        <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn"
                        onclick="document.getElementById('modal-sanction').style.display='none'">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-danger">⚖️ Prononcer la sanction</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal : lever une sanction --}}
    <div class="modal-overlay" id="modal-lever" style="display:none">
        <div class="modal modal-sm">
            <div class="modal-header">
                <span class="modal-title">Lever la sanction</span>
                <button class="modal-close"
                    onclick="document.getElementById('modal-lever').style.display='none'">✕</button>
            </div>
            <form method="POST" id="form-lever">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Raison de la levée</label>
                        <textarea name="raison" class="form-control" rows="3"
                            placeholder="Ex : Employé a présenté des excuses, comportement corrigé…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn"
                        onclick="document.getElementById('modal-lever').style.display='none'">Annuler</button>
                    <button type="submit" class="btn btn-emerald">↩ Confirmer la levée</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function leverSanction(id) {
                document.getElementById('form-lever').action = '/admin/sanctions/' + id + '/lever';
                document.getElementById('modal-lever').style.display = 'flex';
            }

            function toggleMontant(type) {
                const montant = document.getElementById('zone-montant');
                montant.style.opacity = type === 'retenue_salaire' ? '1' : '.4';
            }
        </script>
    @endpush
@endsection
