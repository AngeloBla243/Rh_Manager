{{-- resources/views/admin/presences/rapport.blade.php --}}
@extends('layouts.admin')
@section('title', 'Rapport de présences')
@section('page-title', 'Rapport mensuel de présences')
@section('page-sub', ucfirst(\Carbon\Carbon::createFromDate($annee, $mois, 1)->locale('fr')->isoFormat('MMMM YYYY')))

@section('topbar-actions')
  <select class="form-control" style="width:auto"
    onchange="window.location='?mois='+this.value.split('-')[1]+'&annee='+this.value.split('-')[0]">
    @for($i = 0; $i < 12; $i++)
      @php $d = \Carbon\Carbon::now()->subMonths($i); @endphp
      <option value="{{ $d->format('Y-m') }}" {{ $d->month==$mois&&$d->year==$annee?'selected':'' }}>
        {{ ucfirst($d->locale('fr')->isoFormat('MMMM YYYY')) }}
      </option>
    @endfor
  </select>
  <a href="{{ route('admin.presences.index') }}" class="btn">← Présences du jour</a>
  <a href="{{ route('admin.pdf.rapport-presences', ['mois'=>$mois,'annee'=>$annee]) }}"
     class="btn btn-primary" target="_blank">
    📄 Exporter en PDF
  </a>
@endsection

@section('content')

{{-- KPI cards --}}
@php
  $avgTaux  = collect($rapport)->avg('taux_presence');
  $totalAbs = collect($rapport)->sum('absences');
  $totalRet = collect($rapport)->sum('retards');
  $joursOuv = collect($rapport)->first()['jours_ouvres'] ?? 0;
@endphp

<div class="stats-grid mb-4">
  <div class="stat-card">
    <div class="stat-label">Employés évalués</div>
    <div class="stat-value">{{ count($rapport) }}</div>
    <div class="stat-sub">Période : {{ ucfirst(\Carbon\Carbon::createFromDate($annee,$mois,1)->locale('fr')->isoFormat('MMMM YYYY')) }}</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Taux de présence moyen</div>
    <div class="stat-value" style="color:{{ $avgTaux>=85?'var(--emerald)':($avgTaux>=70?'var(--amber)':'var(--crimson)') }}">
      {{ round($avgTaux, 1) }}%
    </div>
    <div class="stat-sub">Sur {{ $joursOuv }} jours ouvrés</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Absences totales</div>
    <div class="stat-value" style="color:var(--crimson)">{{ $totalAbs }}</div>
    <div class="stat-sub">Tous employés confondus</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Retards totaux</div>
    <div class="stat-value" style="color:var(--amber)">{{ $totalRet }}</div>
    <div class="stat-sub">Entrées après la limite</div>
  </div>
</div>

{{-- Barre visuelle des taux --}}
<div class="card mb-4">
  <div class="card-header"><div class="card-title">Classement par taux de présence</div></div>
  <div class="card-body" style="display:flex;flex-direction:column;gap:14px">
    @foreach(collect($rapport)->sortByDesc('taux_presence') as $ligne)
      <div style="display:flex;align-items:center;gap:12px">
        <div class="avatar">
          {{ mb_strtoupper(mb_substr($ligne['employe']->nom,0,1).mb_substr($ligne['employe']->prenom,0,1)) }}
        </div>
        <div style="flex:1">
          <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:5px">
            <div>
              <span style="font-weight:500;font-size:13px">
                {{ $ligne['employe']->nom }} {{ $ligne['employe']->prenom }}
              </span>
              <span class="text-sm text-muted" style="margin-left:8px">{{ $ligne['employe']->fonction }}</span>
            </div>
            <div style="display:flex;align-items:center;gap:10px">
              <span class="text-sm text-muted font-mono">
                {{ $ligne['jours_travailles'] }}/{{ $ligne['jours_ouvres'] }} jrs
              </span>
              <span class="font-mono" style="font-size:14px;font-weight:700;
                color:{{ $ligne['taux_presence']>=85?'var(--emerald)':($ligne['taux_presence']>=70?'var(--amber)':'var(--crimson)') }}">
                {{ $ligne['taux_presence'] }}%
              </span>
            </div>
          </div>
          <div class="progress">
            <div class="progress-fill
              {{ $ligne['taux_presence']>=85?'progress-green':($ligne['taux_presence']>=70?'progress-amber':'progress-red') }}"
              style="width:{{ $ligne['taux_presence'] }}%;transition:width .6s ease">
            </div>
          </div>
          <div style="display:flex;gap:12px;margin-top:5px">
            <span class="text-xs text-muted">{{ $ligne['absences'] }} absence(s)</span>
            <span class="text-xs text-muted">{{ $ligne['retards'] }} retard(s)</span>
          </div>
        </div>
      </div>
    @endforeach
  </div>
</div>

{{-- Table détaillée --}}
<div class="card">
  <div class="card-header"><div class="card-title">Tableau récapitulatif détaillé</div></div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Employé</th>
          <th>Jours ouvrés</th>
          <th>Présents</th>
          <th>Absents</th>
          <th>Retards</th>
          <th>Taux</th>
          <th>Appréciation</th>
        </tr>
      </thead>
      <tbody>
        @foreach($rapport as $ligne)
          <tr>
            <td>
              <div class="emp-cell">
                <div class="avatar">
                  {{ mb_strtoupper(mb_substr($ligne['employe']->nom,0,1).mb_substr($ligne['employe']->prenom,0,1)) }}
                </div>
                <div>
                  <div class="emp-name">{{ $ligne['employe']->nom }} {{ $ligne['employe']->prenom }}</div>
                  <div class="emp-sub">{{ $ligne['employe']->matricule }}</div>
                </div>
              </div>
            </td>
            <td class="font-mono text-center">{{ $ligne['jours_ouvres'] }}</td>
            <td class="font-mono text-center" style="color:var(--emerald);font-weight:600">{{ $ligne['jours_travailles'] }}</td>
            <td class="font-mono text-center" style="color:{{ $ligne['absences']>0?'var(--crimson)':'var(--ink3)' }}">
              {{ $ligne['absences'] }}
            </td>
            <td class="font-mono text-center" style="color:{{ $ligne['retards']>0?'var(--amber)':'var(--ink3)' }}">
              {{ $ligne['retards'] }}
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <div class="progress" style="width:70px">
                  <div class="progress-fill
                    {{ $ligne['taux_presence']>=85?'progress-green':($ligne['taux_presence']>=70?'progress-amber':'progress-red') }}"
                    style="width:{{ $ligne['taux_presence'] }}%">
                  </div>
                </div>
                <span class="font-mono text-sm">{{ $ligne['taux_presence'] }}%</span>
              </div>
            </td>
            <td>
              @if($ligne['taux_presence'] >= 95)
                <span class="badge badge-green">Excellent</span>
              @elseif($ligne['taux_presence'] >= 85)
                <span class="badge badge-green">Bon</span>
              @elseif($ligne['taux_presence'] >= 70)
                <span class="badge badge-amber">Moyen</span>
              @else
                <span class="badge badge-red">Insuffisant</span>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="card-footer" style="display:flex;justify-content:space-between;align-items:center">
    <span class="text-sm text-muted">
      Moyenne générale :
      <strong style="color:{{ $avgTaux>=85?'var(--emerald)':($avgTaux>=70?'var(--amber)':'var(--crimson)') }}">
        {{ round($avgTaux,1) }}%
      </strong>
    </span>
    <a href="{{ route('admin.pdf.rapport-presences', ['mois'=>$mois,'annee'=>$annee]) }}"
       class="btn btn-sm btn-primary" target="_blank">
      📄 Télécharger le rapport PDF
    </a>
  </div>
</div>
@endsection
