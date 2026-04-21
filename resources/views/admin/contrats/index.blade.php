{{-- resources/views/admin/contrats/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Contrats')
@section('page-title', 'Gestion des contrats')
@section('page-sub', 'Suivi des contrats de travail du personnel')

@section('topbar-actions')
  <select class="form-control" style="width:auto"
    onchange="window.location='?type='+this.value+'&statut={{ request('statut') }}'">
    <option value="">Tous les types</option>
    @foreach(['CDI','CDD','Stage','Interim','Freelance','Autre'] as $t)
      <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ $t }}</option>
    @endforeach
  </select>
  <a href="{{ route('admin.contrats.create') }}" class="btn btn-primary">
    <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2">
      <path d="M8 3v10M3 8h10"/>
    </svg>
    Nouveau contrat
  </a>
@endsection

@section('content')

{{-- Alertes --}}
@if($expirantBientot->count() > 0)
  <div class="alert alert-warning mb-4">
    <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
      <path d="M8 2L1 13h14L8 2z"/><path d="M8 7v3M8 11.5h.01"/>
    </svg>
    <div>
      <strong>{{ $expirantBientot->count() }} contrat(s) expirent dans moins de 30 jours :</strong>
      {{ $expirantBientot->map(fn($c) => $c->employe->nom_complet . ' (' . $c->type . ' — J-' . $c->jours_restants . ')')->join(', ') }}
    </div>
  </div>
@endif

@if($aMettreAJour->count() > 0)
  <div class="alert alert-danger mb-4" style="justify-content:space-between">
    <div style="display:flex;align-items:center;gap:8px">
      <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
        <circle cx="8" cy="8" r="6.5"/><path d="M8 5v3M8 11h.01"/>
      </svg>
      {{ $aMettreAJour->count() }} contrat(s) arrivé(s) à terme non mis à jour.
    </div>
    <form method="POST" action="{{ route('admin.contrats.expiration') }}">
      @csrf
      <button type="submit" class="btn btn-sm btn-danger">Mettre à jour automatiquement</button>
    </form>
  </div>
@endif

{{-- Stats --}}
<div class="stats-grid mb-4">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--emerald-bg)">
      <svg width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="var(--emerald)" stroke-width="1.6">
        <path d="M9.5 1.5H3a1 1 0 00-1 1v11a1 1 0 001 1h10a1 1 0 001-1V5.5z"/>
        <path d="M9 1.5V6h4.5M5 9h6M5 11.5h4"/>
      </svg>
    </div>
    <div class="stat-label">Contrats actifs</div>
    <div class="stat-value">{{ $stats['total'] }}</div>
    <div class="stat-sub">En vigueur actuellement</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">CDI</div>
    <div class="stat-value" style="color:var(--emerald)">{{ $stats['cdi'] }}</div>
    <div class="stat-sub">Durée indéterminée</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">CDD</div>
    <div class="stat-value" style="color:var(--cobalt)">{{ $stats['cdd'] }}</div>
    <div class="stat-sub">Durée déterminée</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Stages</div>
    <div class="stat-value" style="color:var(--amber)">{{ $stats['stages'] }}</div>
    @if($stats['expirant_bientot'] > 0)
      <div class="stat-sub stat-down">⚠ {{ $stats['expirant_bientot'] }} expiration(s) proche(s)</div>
    @else
      <div class="stat-sub">Aucune expiration proche</div>
    @endif
  </div>
</div>

{{-- Table --}}
<div class="card">
  <div class="card-header">
    <div class="card-title">Liste des contrats</div>
    <div style="display:flex;gap:6px">
      @foreach(['' => 'Tous', 'actif' => 'Actifs', 'expire' => 'Expirés', 'resilie' => 'Résiliés'] as $val => $label)
        <a href="?statut={{ $val }}&type={{ request('type') }}"
           class="btn btn-sm {{ request('statut', '') === $val ? 'btn-primary' : '' }}">
          {{ $label }}
        </a>
      @endforeach
    </div>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Employé</th>
          <th>Type</th>
          <th>Poste</th>
          <th>Début</th>
          <th>Fin</th>
          <th>Durée</th>
          <th>Salaire</th>
          <th>Période essai</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($contrats as $c)
          <tr>
            <td>
              <div class="emp-cell">
                <div class="avatar">{{ $c->employe->initiales }}</div>
                <div>
                  <div class="emp-name">{{ $c->employe->nom }} {{ $c->employe->prenom }}</div>
                  <div class="emp-sub">{{ $c->employe->matricule }}</div>
                </div>
              </div>
            </td>
            <td>
              <span class="badge {{ match($c->type) {
                'CDI'   => 'badge-green',
                'CDD'   => 'badge-blue',
                'Stage' => 'badge-amber',
                default => 'badge-gray'
              } }}">{{ $c->type }}</span>
            </td>
            <td class="text-sm">{{ $c->poste ?? $c->employe->fonction }}</td>
            <td class="font-mono text-sm">{{ $c->date_debut->format('d/m/Y') }}</td>
            <td class="font-mono text-sm">
              @if($c->date_fin)
                <span style="color:{{ $c->expire_bientot ? 'var(--amber)' : ($c->est_expire ? 'var(--crimson)' : 'inherit') }}">
                  {{ $c->date_fin->format('d/m/Y') }}
                  @if($c->expire_bientot)
                    <span class="badge badge-amber" style="font-size:9px;margin-left:2px">J-{{ $c->jours_restants }}</span>
                  @endif
                </span>
              @else
                <span class="text-muted">Indéterminée</span>
              @endif
            </td>
            <td class="text-sm text-muted">{{ $c->duree_formatee }}</td>
            <td class="font-mono text-sm">
              {{ $c->salaire_contractuel ? number_format($c->salaire_contractuel, 2, ',', ' ').' $' : '—' }}
            </td>
            <td>
              @if($c->periode_essai)
                @if($c->en_periode_essai)
                  <span class="badge badge-amber">En cours</span>
                @else
                  <span class="badge badge-gray text-xs">Terminée</span>
                @endif
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td>
              <span class="badge badge-{{ $c->statut_couleur }}">{{ $c->statut_libelle }}</span>
            </td>
            <td>
              <div class="table-actions">
                <a href="{{ route('admin.contrats.show', $c) }}" class="btn btn-sm">Voir</a>
                <a href="{{ route('admin.contrats.edit', $c) }}" class="btn btn-sm">✎</a>
                @if($c->statut === 'actif' && $c->type !== 'CDI')
                  <button class="btn btn-sm btn-emerald" onclick="renouveler({{ $c->id }})">↻</button>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="10">
              <div class="empty">
                <div class="empty-icon">📋</div>
                <div class="empty-title">Aucun contrat enregistré</div>
                <div class="empty-sub">Créez le premier contrat de votre personnel.</div>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($contrats->hasPages())
    <div class="card-footer" style="display:flex;justify-content:flex-end">
      {{ $contrats->links() }}
    </div>
  @endif
</div>

{{-- Modal renouvellement --}}
<div class="modal-overlay" id="modal-renouveler" style="display:none">
  <div class="modal modal-sm">
    <div class="modal-header">
      <span class="modal-title">Renouveler le contrat</span>
      <button class="modal-close" onclick="document.getElementById('modal-renouveler').style.display='none'">✕</button>
    </div>
    <form method="POST" id="form-renouveler">
      @csrf
      <div class="modal-body">
        <div class="alert alert-info mb-4">
          Le contrat actuel sera marqué comme « Renouvelé » et un nouveau contrat sera créé à la suite.
        </div>
        <div class="form-group">
          <label class="form-label">Nouvelle date de fin <span class="required">*</span></label>
          <input type="date" name="nouvelle_date_fin" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" onclick="document.getElementById('modal-renouveler').style.display='none'">Annuler</button>
        <button type="submit" class="btn btn-primary">↻ Renouveler</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function renouveler(id) {
  document.getElementById('form-renouveler').action = '/admin/contrats/' + id + '/renouveler';
  document.getElementById('modal-renouveler').style.display = 'flex';
}
</script>
@endpush
@endsection
