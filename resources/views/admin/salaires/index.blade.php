{{-- resources/views/admin/salaires/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Salaires')
@section('page-title', 'Gestion des salaires')
@section('page-sub', 'Calcul et suivi des rémunérations')

@section('topbar-actions')
  <select class="form-control" style="width:auto" onchange="window.location='?mois='+this.value.split('-')[1]+'&annee='+this.value.split('-')[0]">
    @for($i=0;$i<12;$i++)
      @php $d=\Carbon\Carbon::now()->subMonths($i); @endphp
      <option value="{{ $d->format('Y-m') }}" {{ $d->month==$mois&&$d->year==$annee?'selected':'' }}>
        {{ ucfirst($d->locale('fr')->isoFormat('MMMM YYYY')) }}
      </option>
    @endfor
  </select>
  <form method="POST" action="{{ route('admin.salaires.calculer') }}" style="display:inline">
    @csrf
    <input type="hidden" name="mois" value="{{ $mois }}">
    <input type="hidden" name="annee" value="{{ $annee }}">
    <button type="submit" class="btn" onclick="return confirm('Recalculer tous les salaires du mois ?')">
      ↻ Recalculer
    </button>
  </form>
  <a href="{{ route('admin.pdf.fiches-collectives', ['mois'=>$mois,'annee'=>$annee]) }}" class="btn btn-primary" target="_blank">
    📄 Générer toutes les fiches PDF
  </a>
@endsection

@section('content')

<div class="stats-grid mb-4">
  <div class="stat-card">
    <div class="stat-label">Salaires bruts</div>
    <div class="stat-value money">{{ number_format($totalBrut,0,',',' ') }} $</div>
    <div class="stat-sub">Total brut du mois</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Total pénalités</div>
    <div class="stat-value money" style="color:var(--crimson)">- {{ number_format($penalites,0,',',' ') }} $</div>
    <div class="stat-sub stat-down">Déductions sur absences/retards</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Net à payer total</div>
    <div class="stat-value money" style="color:var(--emerald)">{{ number_format($totalNet,0,',',' ') }} $</div>
    <div class="stat-sub stat-up">Masse salariale nette</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Budget annuel (projeté)</div>
    <div class="stat-value money">{{ number_format($totalNet*12,0,',',' ') }} $</div>
    <div class="stat-sub">Projection sur 12 mois</div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">
      Fiches de salaire —
      {{ ucfirst(\Carbon\Carbon::createFromDate($annee,$mois,1)->locale('fr')->isoFormat('MMMM YYYY')) }}
    </div>
    <div class="text-sm text-muted">{{ $salaires->count() }} fiche(s) générée(s)</div>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Employé</th>
          <th>Salaire brut</th>
          <th>Jours trav.</th>
          <th>Absences</th>
          <th>Pénalités</th>
          <th>Net à payer</th>
          <th>Taux</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($salaires as $s)
          <tr>
            <td>
              <div class="emp-cell">
                <div class="avatar">{{ mb_strtoupper(mb_substr($s->employe->nom,0,1).mb_substr($s->employe->prenom,0,1)) }}</div>
                <div>
                  <div class="emp-name">{{ $s->employe->nom }} {{ $s->employe->prenom }}</div>
                  <div class="emp-sub">{{ $s->employe->fonction }}</div>
                </div>
              </div>
            </td>
            <td class="font-mono">{{ number_format($s->salaire_brut,2,',',' ') }} $</td>
            <td class="font-mono text-sm">
              {{ $s->jours_travailles }} / {{ $s->jours_ouvres }}
            </td>
            <td class="font-mono text-sm" style="color:{{ $s->nb_absences>0?'var(--crimson)':'var(--ink3)' }}">
              {{ $s->nb_absences }}
            </td>
            <td class="font-mono text-sm" style="color:{{ $s->total_penalites>0?'var(--crimson)':'var(--ink3)' }}">
              {{ $s->total_penalites > 0 ? '- ' . number_format($s->total_penalites,2,',',' ') . ' $' : '—' }}
            </td>
            <td class="font-mono" style="font-weight:600;color:var(--emerald)">
              {{ number_format($s->salaire_net,2,',',' ') }} $
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:6px">
                <div class="progress" style="width:60px"><div class="progress-fill {{ $s->taux_presence>=90?'progress-green':($s->taux_presence>=70?'progress-amber':'progress-red') }}" style="width:{{ $s->taux_presence }}%"></div></div>
                <span class="font-mono text-xs">{{ $s->taux_presence }}%</span>
              </div>
            </td>
            <td>
              @if($s->statut_paiement==='paye')
                <span class="badge badge-green">Payé le {{ $s->date_paiement->format('d/m') }}</span>
              @elseif($s->statut_paiement==='annule')
                <span class="badge badge-red">Annulé</span>
              @else
                <span class="badge badge-amber">En attente</span>
              @endif
            </td>
            <td>
              <div class="table-actions">
                <a href="{{ route('admin.pdf.fiche-individuelle',$s) }}" class="btn btn-sm" target="_blank">PDF</a>
                @if($s->statut_paiement==='en_attente')
                  <form method="POST" action="{{ route('admin.salaires.paye',$s) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-emerald">✓ Payé</button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="9">
            <div class="empty">
              <div class="empty-title">Aucune fiche de salaire</div>
              <div class="empty-sub">Cliquez sur « Recalculer » pour générer les salaires du mois.</div>
            </div>
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
