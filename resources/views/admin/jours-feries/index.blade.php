{{-- resources/views/admin/jours-feries/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Jours fériés')
@section('page-title', 'Jours fériés')
@section('page-sub', 'Gestion des jours non travaillés — ' . ($annee ?? now()->year))

@section('topbar-actions')
    <select class="form-control" style="width:auto" onchange="window.location='?annee='+this.value">
        @for ($y = now()->year + 1; $y >= now()->year - 2; $y--)
            <option value="{{ $y }}" {{ ($annee ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}
            </option>
        @endfor
    </select>
    <button class="btn" onclick="document.getElementById('modal-dupliquer').style.display='flex'">
        ↻ Dupliquer vers {{ ($annee ?? now()->year) + 1 }}
    </button>
    <button class="btn btn-primary" onclick="document.getElementById('modal-ajouter').style.display='flex'">
        + Ajouter un jour férié
    </button>
@endsection

@section('content')

    <div class="grid grid-2 mb-4">
        {{-- Calendrier visuel --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">{{ count($joursFeries) }} jours fériés en {{ $annee }}</div>
                <span class="tag">Samedi & Dimanche non travaillés</span>
            </div>
            <div style="padding:0">
                @forelse($joursFeries->sortBy('date') as $jf)
                    <div
                        style="display:flex;align-items:center;justify-content:space-between;
                    padding:10px 18px;border-bottom:1px solid var(--border)">
                        <div style="display:flex;align-items:center;gap:14px">
                            <div
                                style="font-family:var(--mono);font-size:11px;font-weight:700;
                        color:var(--cobalt);min-width:50px">
                                {{ $jf->date->format('d M') }}
                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:500">{{ $jf->libelle }}</div>
                                <div class="text-xs text-muted">
                                    {{ ucfirst($jf->date->locale('fr')->isoFormat('dddd')) }}
                                    @if ($jf->recurrent)
                                        · <span style="color:var(--cobalt)">Récurrent</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div style="display:flex;gap:6px;align-items:center">
                            @if ($jf->est_cette_semaine)
                                <span class="badge badge-blue">Cette semaine</span>
                            @elseif($jf->jours_restants > 0)
                                <span class="text-xs text-muted font-mono">J-{{ $jf->jours_restants }}</span>
                            @elseif($jf->jours_restants < 0)
                                <span class="text-xs text-muted">Passé</span>
                            @else
                                <span class="badge badge-amber">Aujourd'hui</span>
                            @endif
                            <form method="POST" action="{{ route('admin.jours-feries.destroy', $jf) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger"
                                    onclick="return confirm('Supprimer ce jour férié ?')">✕</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="empty" style="padding:32px">
                        <div class="empty-icon">📅</div>
                        <div class="empty-title">Aucun jour férié configuré pour {{ $annee }}</div>
                        <div class="empty-sub">Ajoutez des jours fériés ou dupliquez depuis l'année précédente.</div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Infos & jours de repos --}}
        <div>
            <div class="card mb-4">
                <div class="card-header">
                    <div class="card-title">Jours de repos hebdomadaires</div>
                </div>
                <div class="card-body">
                    <div style="display:flex;flex-direction:column;gap:10px">
                        @foreach ([['Samedi', true], ['Dimanche', true], ['Lundi', '—'], ['Mardi', '—'], ['Mercredi', '—'], ['Jeudi', '—'], ['Vendredi', '—']] as [$jour, $repos])
                            <div
                                style="display:flex;align-items:center;justify-content:space-between;
                        padding:8px 12px;background:{{ $repos === true ? 'var(--bg2)' : 'var(--surface)' }};
                        border-radius:var(--r);border:1px solid var(--border)">
                                <span style="font-size:13px">{{ $jour }}</span>
                                @if ($repos === true)
                                    <span class="badge badge-gray">Non travaillé</span>
                                @else
                                    <span class="badge badge-green">Travaillé</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Résumé {{ $annee }}</div>
                </div>
                <div class="card-body">
                    @php
                        $joursCalendrier = 365 + (date('L', mktime(0, 0, 0, 1, 1, $annee)) ? 1 : 0);
                        $weekends = collect(range(1, $joursCalendrier))
                            ->filter(function ($j) use ($annee) {
                                $d = \Carbon\Carbon::createFromDate($annee, 1, 1)->addDays($j - 1);
                                return $d->isWeekend();
                            })
                            ->count();
                        $feries = count($joursFeries);
                        $ouvres = $joursCalendrier - $weekends - $feries;
                    @endphp
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-item-label">Jours calendrier</span>
                            <span class="info-item-val font-mono">{{ $joursCalendrier }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Week-ends</span>
                            <span class="info-item-val font-mono">{{ $weekends }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label">Jours fériés</span>
                            <span class="info-item-val font-mono">{{ $feries }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-item-label" style="font-weight:600">Jours ouvrés estimés</span>
                            <span class="info-item-val font-mono" style="color:var(--emerald);font-weight:700">≈
                                {{ $ouvres }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal : ajouter --}}
    <div class="modal-overlay" id="modal-ajouter" style="display:none">
        <div class="modal modal-sm">
            <div class="modal-header">
                <span class="modal-title">Ajouter un jour férié</span>
                <button class="modal-close"
                    onclick="document.getElementById('modal-ajouter').style.display='none'">✕</button>
            </div>
            <form method="POST" action="{{ route('admin.jours-feries.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Date <span class="required">*</span></label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Libellé <span class="required">*</span></label>
                        <input type="text" name="libelle" class="form-control" required
                            placeholder="Ex : Fête nationale">
                    </div>
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;margin-top:8px">
                        <input type="checkbox" name="recurrent" value="1" checked style="accent-color:var(--ink)">
                        Récurrent chaque année (copié automatiquement)
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn"
                        onclick="document.getElementById('modal-ajouter').style.display='none'">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal : dupliquer --}}
    <div class="modal-overlay" id="modal-dupliquer" style="display:none">
        <div class="modal modal-sm">
            <div class="modal-header">
                <span class="modal-title">Dupliquer vers {{ ($annee ?? now()->year) + 1 }}</span>
                <button class="modal-close"
                    onclick="document.getElementById('modal-dupliquer').style.display='none'">✕</button>
            </div>
            <form method="POST" action="{{ route('admin.jours-feries.dupliquer') }}">
                @csrf
                <input type="hidden" name="annee_source" value="{{ $annee ?? now()->year }}">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <circle cx="8" cy="8" r="6.5" />
                            <path d="M8 7v4M8 5h.01" />
                        </svg>
                        Seuls les jours fériés marqués « Récurrent » seront copiés vers {{ ($annee ?? now()->year) + 1 }}.
                        Les jours déjà existants ne seront pas écrasés.
                    </div>
                    <div class="text-sm text-muted">
                        <strong>{{ $joursFeries->where('recurrent', true)->count() }}</strong> jour(s) récurrent(s) à
                        dupliquer.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn"
                        onclick="document.getElementById('modal-dupliquer').style.display='none'">Annuler</button>
                    <button type="submit" class="btn btn-primary">↻ Dupliquer</button>
                </div>
            </form>
        </div>
    </div>

@endsection
