{{-- resources/views/admin/statistiques/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Statistiques')
@section('page-title', 'Statistiques & Rapports')
@section('page-sub', 'Analyses de présence, financières et RH')

@section('topbar-actions')
  <select class="form-control" style="width:auto" onchange="window.location='?annee='+this.value">
    @for($y=now()->year;$y>=now()->year-3;$y--)
      <option value="{{ $y }}" {{ ($annee??now()->year)==$y?'selected':'' }}>{{ $y }}</option>
    @endfor
  </select>
  <a href="{{ route('admin.pdf.rapport-presences', ['mois'=>now()->month,'annee'=>$annee??now()->year]) }}" class="btn btn-primary" target="_blank">
    📊 Exporter PDF
  </a>
@endsection

@section('content')

<div class="stats-grid mb-4">
  <div class="stat-card">
    <div class="stat-label">Taux présence moyen (année)</div>
    <div class="stat-value">{{ $tauxAnnuel ?? '—' }}%</div>
    <div class="stat-sub">Tous employés confondus</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Absences totales (année)</div>
    <div class="stat-value" style="color:var(--crimson)">{{ $absencesAnnee ?? 0 }}</div>
    <div class="stat-sub">dont {{ $absencesNonJustifiees ?? 0 }} non justifiées</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Dépenses salariales (année)</div>
    <div class="stat-value money">{{ number_format($depensesAnnee ?? 0, 0, ',', ' ') }} $</div>
    <div class="stat-sub">Net versé</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Économies (pénalités)</div>
    <div class="stat-value money" style="color:var(--amber)">{{ number_format($penalitesAnnee ?? 0, 0, ',', ' ') }} $</div>
    <div class="stat-sub">Total des déductions</div>
  </div>
</div>

<div class="grid grid-2 mb-4">
  {{-- Présences par mois --}}
  <div class="card">
    <div class="card-header"><div class="card-title">Taux de présence mensuel — {{ $annee ?? now()->year }}</div></div>
    <div class="card-body">
      <div class="bar-chart" style="height:120px">
        @php
          $moisLabels = ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Aoû','Sep','Oct','Nov','Déc'];
        @endphp
        @foreach($presencesParMois ?? array_fill(0,12,0) as $i => $taux)
          <div class="bar-col">
            <div class="bar-lbl" style="margin-bottom:2px">{{ $taux }}%</div>
            <div class="bar {{ $i === now()->month-1 ? 'active-bar' : '' }}"
                 style="height:{{ min(max($taux,2),100) }}px;
                        background:{{ $taux<70?'var(--crimson-bg)':($taux<85?'var(--amber-bg)':($i===now()->month-1?'var(--ink)':'var(--bg3)')) }}">
            </div>
            <div class="bar-lbl">{{ $moisLabels[$i] }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Dépenses par mois --}}
  <div class="card">
    <div class="card-header"><div class="card-title">Dépenses salariales mensuelles ($)</div></div>
    <div class="card-body">
      <div class="bar-chart" style="height:120px">
        @foreach($depensesParMois ?? array_fill(0,12,0) as $i => $dep)
          @php $max = max(max($depensesParMois ?? [1]),1); @endphp
          <div class="bar-col">
            <div class="bar-lbl">{{ $dep>0 ? number_format($dep/1000,1).'k' : '—' }}</div>
            <div class="bar" style="height:{{ $dep>0 ? round($dep/$max*100) : 2 }}px;background:var(--cobalt-bg)"></div>
            <div class="bar-lbl">{{ $moisLabels[$i] }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

{{-- Taux par employé --}}
<div class="card">
  <div class="card-header">
    <div class="card-title">Taux de présence par employé — {{ $annee ?? now()->year }}</div>
    <div class="text-sm text-muted">Classé par performance</div>
  </div>
  <div class="card-body" style="display:flex;flex-direction:column;gap:14px">
    @forelse($tauxParEmploye ?? [] as $ligne)
      <div style="display:flex;align-items:center;gap:12px">
        <div class="avatar">{{ mb_strtoupper(mb_substr($ligne['employe']->nom,0,1).mb_substr($ligne['employe']->prenom,0,1)) }}</div>
        <div style="flex:1">
          <div style="display:flex;justify-content:space-between;margin-bottom:5px">
            <div>
              <span style="font-weight:500;font-size:13px">{{ $ligne['employe']->nom }} {{ $ligne['employe']->prenom }}</span>
              <span class="text-sm text-muted" style="margin-left:8px">{{ $ligne['employe']->fonction }}</span>
            </div>
            <div class="font-mono" style="font-size:13px;font-weight:600;color:{{ $ligne['taux']>=90?'var(--emerald)':($ligne['taux']>=70?'var(--amber)':'var(--crimson)') }}">
              {{ $ligne['taux'] }}%
            </div>
          </div>
          <div class="progress">
            <div class="progress-fill {{ $ligne['taux']>=90?'progress-green':($ligne['taux']>=70?'progress-amber':'progress-red') }}"
                 style="width:{{ $ligne['taux'] }}%"></div>
          </div>
          <div class="text-xs text-muted mt-1">
            {{ $ligne['jours_travailles'] }} jrs travaillés — {{ $ligne['nb_absences'] }} absence(s)
          </div>
        </div>
      </div>
    @empty
      <div class="empty"><div class="empty-title">Données insuffisantes</div><div class="empty-sub">Calculez d'abord les salaires pour générer les statistiques.</div></div>
    @endforelse
  </div>
</div>
@endsection


{{-- ============================================================
     resources/views/admin/parametres/index.blade.php
     ============================================================ --}}
