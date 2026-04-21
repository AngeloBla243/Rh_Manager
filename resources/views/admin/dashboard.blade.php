{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')
@section('page-sub', 'Vue d\'ensemble — ' . \Carbon\Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY'))

@section('topbar-actions')
  <select class="form-control" style="width:auto;font-size:12px;padding:5px 30px 5px 10px"
    onchange="window.location='?mois='+this.value.split('-')[1]+'&annee='+this.value.split('-')[0]">
    @for($i = 0; $i < 6; $i++)
      @php $d = \Carbon\Carbon::now()->subMonths($i); @endphp
      <option value="{{ $d->format('Y-m') }}" {{ $d->month == $mois && $d->year == $annee ? 'selected' : '' }}>
        {{ ucfirst($d->locale('fr')->isoFormat('MMMM YYYY')) }}
      </option>
    @endfor
  </select>
  <a href="{{ route('admin.employes.create') }}" class="btn btn-primary">
    <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M8 3v10M3 8h10"/></svg>
    Nouvel employé
  </a>
@endsection

@section('content')

{{-- ── Stat cards ──────────────────────────────────────── --}}
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--bg2)">
      <svg width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="var(--ink2)" stroke-width="1.6">
        <circle cx="6" cy="5" r="2.5"/><path d="M1 14c0-2.8 2.2-5 5-5s5 2.2 5 5"/>
        <circle cx="12" cy="4" r="1.8"/><path d="M14 11.5c0-1.7-1-3-2.5-3.5"/>
      </svg>
    </div>
    <div class="stat-label">Total employés actifs</div>
    <div class="stat-value">{{ $totalEmployes }}</div>
    <div class="stat-sub">Personnel en activité</div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:var(--emerald-bg)">
      <svg width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="var(--emerald)" stroke-width="1.6">
        <circle cx="8" cy="8" r="6.2"/><path d="M5 8l2 2 4-4"/>
      </svg>
    </div>
    <div class="stat-label">Présents aujourd'hui</div>
    <div class="stat-value">{{ $presentsAujourdhui }}
      <span style="font-size:15px;font-weight:400;color:var(--ink3)">/ {{ $totalEmployes }}</span>
    </div>
    <div class="stat-sub">
      @if($totalEmployes > 0)
        <span class="{{ $presentsAujourdhui / $totalEmployes >= .8 ? 'stat-up' : 'stat-down' }}">
          {{ round($presentsAujourdhui / $totalEmployes * 100) }}% de présence
        </span>
      @endif
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:var(--cobalt-bg)">
      <svg width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="var(--cobalt)" stroke-width="1.6">
        <path d="M8 1v14M5 4.5h4a2 2 0 010 4H5h4.5a2.5 2.5 0 010 5H4"/>
      </svg>
    </div>
    <div class="stat-label">Masse salariale</div>
    <div class="stat-value money">{{ number_format($masseSalariale, 0, ',', ' ') }} $</div>
    <div class="stat-sub">Net à payer ce mois</div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:var(--crimson-bg)">
      <svg width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="var(--crimson)" stroke-width="1.6">
        <rect x="1.5" y="3" width="13" height="11" rx="1.5"/>
        <path d="M5 1.5V4M11 1.5V4M1.5 7h13M5 10h2M9 10h2M5 12.5h2"/>
      </svg>
    </div>
    <div class="stat-label">Absents aujourd'hui</div>
    <div class="stat-value" style="color:var(--crimson)">{{ $absentsAujourdhui }}</div>
    <div class="stat-sub stat-down">{{ $absentsAujourdhui }} employé(s) manquant(s)</div>
  </div>
</div>

{{-- ── Graphiques ──────────────────────────────────────── --}}
<div class="grid grid-2 mb-4">
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Taux de présence mensuel</div>
        <div class="section-sub">6 derniers mois</div>
      </div>
      <span class="tag">{{ ucfirst(\Carbon\Carbon::now()->locale('fr')->isoFormat('MMMM YYYY')) }}</span>
    </div>
    <div class="card-body">
      <div class="bar-chart" style="height:100px;align-items:flex-end">
        @foreach($graphPresences as $g)
          <div class="bar-col">
            <div style="font-size:10px;color:var(--ink3);font-family:var(--mono)">{{ $g['taux'] }}%</div>
            <div class="bar {{ $loop->last ? 'active-bar' : '' }}"
                 style="height:{{ $g['taux'] }}px;min-height:6px;
                        background:{{ $loop->last ? 'var(--ink)' : ($g['taux'] < 70 ? 'var(--crimson-bg)' : 'var(--bg3)') }}">
            </div>
            <div class="bar-lbl">{{ mb_substr($g['mois'], 0, 3) }}</div>
          </div>
        @endforeach
      </div>
      <div style="display:flex;justify-content:space-between;margin-top:12px;font-size:11.5px;color:var(--ink3)">
        <span>Moyenne : {{ round(collect($graphPresences)->avg('taux'), 1) }}%</span>
        <span>Objectif : 90%</span>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Présences du jour</div>
        <div class="section-sub">{{ \Carbon\Carbon::today()->locale('fr')->isoFormat('dddd D MMMM') }}</div>
      </div>
      <div class="donut" style="--p:{{ $totalEmployes > 0 ? round($presentsAujourdhui / $totalEmployes * 360) : 0 }}deg">
        <div class="donut-val">{{ $totalEmployes > 0 ? round($presentsAujourdhui / $totalEmployes * 100) : 0 }}%</div>
      </div>
    </div>
    <div class="card-body">
      <div style="display:flex;flex-direction:column;gap:10px">
        @php
          $items = [
            ['Présents',  $presentsAujourdhui,   'emerald'],
            ['Absents',   $absentsAujourdhui,     'crimson'],
            ['Retards',   $retardsAujourdhui ?? 0,'amber'],
          ];
        @endphp
        @foreach($items as [$label, $val, $color])
          <div>
            <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:4px">
              <span style="color:var(--ink2)">{{ $label }}</span>
              <span style="font-weight:600;color:var(--ink);font-family:var(--mono)">{{ $val }}</span>
            </div>
            <div class="progress">
              <div class="progress-fill progress-{{ $color }}"
                   style="width:{{ $totalEmployes > 0 ? round($val / $totalEmployes * 100) : 0 }}%">
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

{{-- ── Tableau présences du jour ───────────────────────── --}}
<div class="card">
  <div class="card-header">
    <div class="card-title">Registre du jour</div>
    <div class="flex gap-2">
      <a href="{{ route('admin.presences.index') }}" class="btn btn-sm">Voir tout →</a>
      <a href="{{ route('admin.presences.manuel') }}" class="btn btn-sm btn-primary">Pointer manuellement</a>
    </div>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Employé</th>
          <th>Entrée</th>
          <th>Sortie</th>
          <th>Durée</th>
          <th>Statut</th>
        </tr>
      </thead>
      <tbody>
        @forelse($presencesJour ?? [] as $p)
          <tr>
            <td>
              <div class="emp-cell">
                <div class="avatar">
                  @if($p->employe->photo)
                    <img src="{{ asset('storage/' . $p->employe->photo) }}" alt="">
                  @else
                    {{ mb_strtoupper(mb_substr($p->employe->nom,0,1) . mb_substr($p->employe->prenom,0,1)) }}
                  @endif
                </div>
                <div>
                  <div class="emp-name">{{ $p->employe->nom }} {{ $p->employe->prenom }}</div>
                  <div class="emp-sub">{{ $p->employe->fonction }}</div>
                </div>
              </div>
            </td>
            <td class="font-mono">{{ $p->heure_entree ? \Carbon\Carbon::parse($p->heure_entree)->format('H:i') : '—' }}</td>
            <td class="font-mono">{{ $p->heure_sortie ? \Carbon\Carbon::parse($p->heure_sortie)->format('H:i') : '—' }}</td>
            <td class="font-mono">
              @if($p->heure_entree && $p->heure_sortie)
                {{ \Carbon\Carbon::parse($p->heure_entree)->diff(\Carbon\Carbon::parse($p->heure_sortie))->format('%Hh%I') }}
              @else —
              @endif
            </td>
            <td>
              @if(!$p->heure_entree)
                <span class="badge badge-red"><span class="badge-dot" style="background:var(--crimson)"></span>Absent</span>
              @elseif($p->est_retard)
                <span class="badge badge-amber"><span class="badge-dot" style="background:var(--amber)"></span>Retard {{ $p->minutes_retard }}min</span>
              @elseif($p->est_valide)
                <span class="badge badge-green"><span class="badge-dot" style="background:var(--emerald)"></span>Présent</span>
              @else
                <span class="badge badge-blue">En cours</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5">
              <div class="empty" style="padding:30px">
                <div class="empty-title">Aucune présence enregistrée</div>
                <div class="empty-sub">Les pointages du jour apparaîtront ici.</div>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
