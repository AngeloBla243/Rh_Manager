{{-- resources/views/admin/contrats/create.blade.php --}}
@extends('layouts.admin')
@section('title', 'Nouveau contrat')
@section('page-title', 'Nouveau contrat')
@section('page-sub', 'Enregistrement d\'un contrat de travail')

@section('topbar-actions')
  <a href="{{ route('admin.contrats.index') }}" class="btn">← Retour</a>
@endsection

@section('content')
<div style="max-width:720px">
  @if($errors->any())
    <div class="alert alert-danger mb-4">
      @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('admin.contrats.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="card mb-4">
      <div class="card-header"><div class="card-title">Informations du contrat</div></div>
      <div class="card-body">

        <div class="form-group">
          <label class="form-label">Employé <span class="required">*</span></label>
          <select name="employe_id" class="form-control" required>
            <option value="">— Sélectionner un employé —</option>
            @foreach($employes as $e)
              <option value="{{ $e->id }}"
                {{ (old('employe_id', $employeId) == $e->id) ? 'selected' : '' }}>
                {{ $e->nom }} {{ $e->prenom }} — {{ $e->matricule }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">Type de contrat <span class="required">*</span></label>
            <select name="type" class="form-control" required id="type-select"
                    onchange="toggleDateFin(this.value)">
              @foreach(['CDI','CDD','Stage','Interim','Freelance','Autre'] as $t)
                <option value="{{ $t }}" {{ old('type') === $t ? 'selected' : '' }}>{{ $t }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Statut</label>
            <select name="statut" class="form-control">
              <option value="actif">Actif</option>
              <option value="suspendu">Suspendu</option>
            </select>
          </div>
        </div>

        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">Date de début <span class="required">*</span></label>
            <input type="date" name="date_debut" class="form-control"
                   value="{{ old('date_debut', now()->toDateString()) }}" required>
          </div>
          <div class="form-group" id="zone-date-fin">
            <label class="form-label">
              Date de fin
              <small class="text-muted" id="label-fin-hint">(obligatoire pour CDD/Stage)</small>
            </label>
            <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin') }}" id="date-fin-input">
          </div>
        </div>

        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">Poste occupé</label>
            <input type="text" name="poste" class="form-control"
                   value="{{ old('poste') }}" placeholder="Ex : Responsable comptable">
            <div class="text-xs text-muted mt-1">Laisser vide pour utiliser la fonction de l'employé</div>
          </div>
          <div class="form-group">
            <label class="form-label">Salaire contractuel ($)</label>
            <input type="number" name="salaire_contractuel" class="form-control"
                   min="0" step="0.01" value="{{ old('salaire_contractuel') }}"
                   placeholder="Si différent du salaire de base">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Numéro de référence du contrat</label>
          <input type="text" name="numero_contrat" class="form-control"
                 value="{{ old('numero_contrat') }}" placeholder="Ex : CTR-2025-001">
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header"><div class="card-title">Période d'essai</div></div>
      <div class="card-body">
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;margin-bottom:12px">
          <input type="checkbox" name="periode_essai" value="1"
                 {{ old('periode_essai') ? 'checked' : '' }}
                 onchange="document.getElementById('zone-essai').style.display=this.checked?'':'none'"
                 style="accent-color:var(--ink)">
          Ce contrat inclut une période d'essai
        </label>
        <div id="zone-essai" style="display:{{ old('periode_essai') ? '' : 'none' }}">
          <div class="form-group">
            <label class="form-label">Fin de la période d'essai</label>
            <input type="date" name="fin_periode_essai" class="form-control" value="{{ old('fin_periode_essai') }}">
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header"><div class="card-title">Document & Notes</div></div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">Document signé (scan du contrat)</label>
          <input type="file" name="document" class="form-control"
                 accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
          <div class="text-xs text-muted mt-1">PDF recommandé — max 10 Mo</div>
        </div>
        <div class="form-group">
          <label class="form-label">Description / Notes contractuelles</label>
          <textarea name="description" class="form-control" rows="3"
                    placeholder="Clauses particulières, avantages, conditions spéciales…">{{ old('description') }}</textarea>
        </div>
      </div>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:8px">
      <a href="{{ route('admin.contrats.index') }}" class="btn">Annuler</a>
      <button type="submit" class="btn btn-primary">
        <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2">
          <path d="M13 3L6 12l-3-3"/>
        </svg>
        Enregistrer le contrat
      </button>
    </div>
  </form>
</div>

@push('scripts')
<script>
function toggleDateFin(type) {
  const required = ['CDD','Stage','Interim'].includes(type);
  const input = document.getElementById('date-fin-input');
  const hint  = document.getElementById('label-fin-hint');
  input.required = required;
  hint.textContent = required ? '(obligatoire)' : '(optionnel pour CDI)';
  hint.style.color = required ? 'var(--crimson)' : 'var(--ink3)';
}
// Init on load
toggleDateFin(document.getElementById('type-select').value);
</script>
@endpush
@endsection


{{-- ============================================================
     resources/views/admin/contrats/show.blade.php
     ============================================================ --}}
