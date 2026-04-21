{{-- ============================================================
     resources/views/admin/employes/index.blade.php
     ============================================================ --}}
@extends('layouts.admin')
@section('title', 'Employés')
@section('page-title', 'Gestion des employés')
@section('page-sub', $employes->total() . ' employé(s) enregistré(s)')

@section('topbar-actions')
  <div class="input-group">
    <input type="text" class="form-control" placeholder="Rechercher un employé…"
           id="search-input" value="{{ request('search') }}"
           style="width:220px;border-radius:var(--r) 0 0 var(--r)">
    <button class="btn" onclick="submitSearch()">
      <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="6.5" cy="6.5" r="5"/><path d="M11 11l3.5 3.5"/>
      </svg>
    </button>
  </div>
  <select class="form-control" style="width:auto" onchange="window.location='?fonction='+this.value+'&search={{ request('search') }}'">
    <option value="">Toutes les fonctions</option>
    @foreach($fonctions ?? [] as $f)
      <option value="{{ $f }}" {{ request('fonction') === $f ? 'selected' : '' }}>{{ $f }}</option>
    @endforeach
  </select>
  <a href="{{ route('admin.employes.create') }}" class="btn btn-primary">
    <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M8 3v10M3 8h10"/></svg>
    Nouvel employé
  </a>
@endsection

@section('content')
<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Employé</th>
          <th>Matricule</th>
          <th>Fonction</th>
          <th>Engagement</th>
          <th>Salaire base</th>
          <th>Statut</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($employes as $e)
          <tr>
            <td>
              <div class="emp-cell">
                <div class="avatar">
                  @if($e->photo)
                    <img src="{{ asset('storage/' . $e->photo) }}" alt="">
                  @else
                    {{ mb_strtoupper(mb_substr($e->nom,0,1).mb_substr($e->prenom,0,1)) }}
                  @endif
                </div>
                <div>
                  <div class="emp-name">{{ $e->nom }} {{ $e->prenom }}</div>
                  <div class="emp-sub">{{ $e->postnom }}</div>
                </div>
              </div>
            </td>
            <td><span class="font-mono" style="font-size:12px;color:var(--ink3)">{{ $e->matricule }}</span></td>
            <td>{{ $e->fonction }}</td>
            <td class="font-mono">{{ $e->annee_engagement }}</td>
            <td class="font-mono" style="font-weight:500;color:var(--ink)">{{ number_format($e->salaire_base, 0, ',', ' ') }} $</td>
            <td>
              @if($e->statut === 'actif')
                <span class="badge badge-green">Actif</span>
              @elseif($e->statut === 'suspendu')
                <span class="badge badge-amber">Suspendu</span>
              @else
                <span class="badge badge-gray">Retraité</span>
              @endif
            </td>
            <td>
              <div class="table-actions">
                <a href="{{ route('admin.employes.show', $e) }}" class="btn btn-sm">Voir</a>
                <a href="{{ route('admin.employes.edit', $e) }}" class="btn btn-sm">
                  <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11.5 2.5l2 2L5 13H3v-2z"/>
                  </svg>
                </a>
                <a href="{{ route('admin.pdf.carte-service', $e) }}" class="btn btn-sm" target="_blank" title="Carte de service">
                  <svg width="12" height="12" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="1" y="3.5" width="14" height="9" rx="1.5"/>
                    <path d="M1 7h14M4 10.5h3"/>
                  </svg>
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7">
              <div class="empty">
                <div class="empty-icon">👤</div>
                <div class="empty-title">Aucun employé trouvé</div>
                <div class="empty-sub">Ajoutez votre premier employé pour commencer.</div>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($employes->hasPages())
    <div class="card-footer" style="display:flex;justify-content:space-between;align-items:center">
      <span style="font-size:12px;color:var(--ink3)">
        {{ $employes->firstItem() }}–{{ $employes->lastItem() }} sur {{ $employes->total() }} résultats
      </span>
      <div class="pagination">
        @if($employes->onFirstPage())
          <span class="page-link disabled">‹</span>
        @else
          <a href="{{ $employes->previousPageUrl() }}" class="page-link">‹</a>
        @endif
        @foreach($employes->getUrlRange(1, $employes->lastPage()) as $page => $url)
          <a href="{{ $url }}" class="page-link {{ $page == $employes->currentPage() ? 'active' : '' }}">{{ $page }}</a>
        @endforeach
        @if($employes->hasMorePages())
          <a href="{{ $employes->nextPageUrl() }}" class="page-link">›</a>
        @else
          <span class="page-link disabled">›</span>
        @endif
      </div>
    </div>
  @endif
</div>

@push('scripts')
<script>
function submitSearch() {
  const q = document.getElementById('search-input').value;
  window.location = '?search=' + encodeURIComponent(q) + '&fonction={{ request('fonction') }}';
}
document.getElementById('search-input').addEventListener('keydown', e => {
  if (e.key === 'Enter') submitSearch();
});
</script>
@endpush
@endsection


{{-- ============================================================
     resources/views/admin/employes/create.blade.php
     ============================================================ --}}
{{-- SAVE AS SEPARATE FILE: resources/views/admin/employes/create.blade.php
@extends('layouts.admin')
@section('title', 'Nouvel employé')
@section('page-title', 'Nouvel employé')
@section('page-sub', 'Enregistrement d\'un nouveau membre du personnel')

@section('topbar-actions')
  <a href="{{ route('admin.employes.index') }}" class="btn">← Retour à la liste</a>
@endsection

@section('content')
<div style="max-width:720px">
  <form method="POST" action="{{ route('admin.employes.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="card mb-4">
      <div class="card-header"><div class="card-title">Informations personnelles</div></div>
      <div class="card-body">
        <div class="form-row form-row-3">
          <div class="form-group">
            <label class="form-label">Nom <span class="required">*</span></label>
            <input type="text" name="nom" class="form-control" value="{{ old('nom') }}" required>
          </div>
          <div class="form-group">
            <label class="form-label">Postnom</label>
            <input type="text" name="postnom" class="form-control" value="{{ old('postnom') }}">
          </div>
          <div class="form-group">
            <label class="form-label">Prénom <span class="required">*</span></label>
            <input type="text" name="prenom" class="form-control" value="{{ old('prenom') }}" required>
          </div>
        </div>
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">Date de naissance <span class="required">*</span></label>
            <input type="date" name="date_naissance" class="form-control" value="{{ old('date_naissance') }}" required>
          </div>
          <div class="form-group">
            <label class="form-label">Photo</label>
            <input type="file" name="photo" class="form-control" accept="image/*">
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header"><div class="card-title">Informations professionnelles</div></div>
      <div class="card-body">
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">Fonction <span class="required">*</span></label>
            <input type="text" name="fonction" class="form-control" value="{{ old('fonction') }}" required>
          </div>
          <div class="form-group">
            <label class="form-label">Année d'engagement <span class="required">*</span></label>
            <input type="number" name="annee_engagement" class="form-control" min="1990" max="{{ date('Y') }}" value="{{ old('annee_engagement', date('Y')) }}" required>
          </div>
        </div>
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">Salaire de base ($) <span class="required">*</span></label>
            <input type="number" name="salaire_base" class="form-control" min="0" step="0.01" value="{{ old('salaire_base') }}" required>
          </div>
          <div class="form-group">
            <label class="form-label">Statut</label>
            <select name="statut" class="form-control">
              <option value="actif">Actif</option>
              <option value="suspendu">Suspendu</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:8px">
      <a href="{{ route('admin.employes.index') }}" class="btn">Annuler</a>
      <button type="submit" class="btn btn-primary">Enregistrer l'employé</button>
    </div>
  </form>
</div>
@endsection
--}}
