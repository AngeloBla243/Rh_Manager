{{-- resources/views/admin/employes/create.blade.php --}}
@extends('layouts.admin')
@section('title', 'Nouvel employé')
@section('page-title', 'Nouvel employé')
@section('page-sub', 'Enregistrement d\'un nouveau membre du personnel')

@section('topbar-actions')
  <a href="{{ route('admin.employes.index') }}" class="btn">← Retour</a>
@endsection

@section('content')
<div style="max-width:720px">
  @if($errors->any())
    <div class="alert alert-danger mb-4">
      <ul style="list-style:none;display:flex;flex-direction:column;gap:4px">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.employes.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="card mb-4">
      <div class="card-header"><div class="card-title">Informations personnelles</div></div>
      <div class="card-body">
        <div class="form-row form-row-3">
          <div class="form-group">
            <label class="form-label">Nom <span class="required">*</span></label>
            <input type="text" name="nom" class="form-control" value="{{ old('nom') }}" required placeholder="Nom de famille">
          </div>
          <div class="form-group">
            <label class="form-label">Postnom</label>
            <input type="text" name="postnom" class="form-control" value="{{ old('postnom') }}" placeholder="Postnom">
          </div>
          <div class="form-group">
            <label class="form-label">Prénom <span class="required">*</span></label>
            <input type="text" name="prenom" class="form-control" value="{{ old('prenom') }}" required placeholder="Prénom">
          </div>
        </div>
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">Date de naissance <span class="required">*</span></label>
            <input type="date" name="date_naissance" class="form-control" value="{{ old('date_naissance') }}" required>
          </div>
          <div class="form-group">
            <label class="form-label">Photo (optionnel)</label>
            <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
            <div class="text-sm text-muted mt-1">JPG, PNG — max 2 Mo</div>
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
            <input type="text" name="fonction" class="form-control" value="{{ old('fonction') }}" required placeholder="Ex : Comptable">
          </div>
          <div class="form-group">
            <label class="form-label">Année d'engagement <span class="required">*</span></label>
            <input type="number" name="annee_engagement" class="form-control"
                   min="1990" max="{{ date('Y') }}" value="{{ old('annee_engagement', date('Y')) }}" required>
          </div>
        </div>
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">Salaire de base ($) <span class="required">*</span></label>
            <input type="number" name="salaire_base" class="form-control"
                   min="0" step="0.01" value="{{ old('salaire_base') }}" required placeholder="0.00">
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

    <div class="card mb-4">
      <div class="card-header">
        <div>
          <div class="card-title">Empreinte digitale</div>
          <div class="section-sub">Enregistrée via l'appareil biométrique connecté</div>
        </div>
      </div>
      <div class="card-body">
        <div class="alert alert-info">
          <svg width="15" height="15" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="8" cy="8" r="6.5"/><path d="M8 7v4M8 5h.01"/>
          </svg>
          L'empreinte sera enregistrée après la création de l'employé, depuis sa fiche individuelle.
        </div>
      </div>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:8px">
      <a href="{{ route('admin.employes.index') }}" class="btn">Annuler</a>
      <button type="submit" class="btn btn-primary">
        <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M13 3L6 12l-3-3"/></svg>
        Enregistrer l'employé
      </button>
    </div>
  </form>
</div>
@endsection
