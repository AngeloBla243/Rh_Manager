{{-- resources/views/admin/employes/edit.blade.php --}}
@extends('layouts.admin')
@section('title', 'Modifier — ' . $employe->nom_complet)
@section('page-title', 'Modifier l\'employé')
@section('page-sub', $employe->matricule . ' — ' . $employe->nom_complet)

@section('topbar-actions')
  <a href="{{ route('admin.employes.show', $employe) }}" class="btn">← Retour à la fiche</a>
@endsection

@section('content')
<div style="max-width:720px">

  @if($errors->any())
    <div class="alert alert-danger mb-4">
      @foreach($errors->all() as $err)
        <div>• {{ $err }}</div>
      @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('admin.employes.update', $employe) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- Informations personnelles --}}
    <div class="card mb-4">
      <div class="card-header">
        <div class="card-title">Informations personnelles</div>
        <span class="badge badge-gray">{{ $employe->matricule }}</span>
      </div>
      <div class="card-body">

        {{-- Photo actuelle --}}
        @if($employe->photo)
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;padding:12px;background:var(--bg2);border-radius:var(--r)">
          <div class="avatar avatar-lg">
            <img src="{{ asset('storage/' . $employe->photo) }}" alt="">
          </div>
          <div>
            <div style="font-size:12.5px;font-weight:500">Photo actuelle</div>
            <div class="text-sm text-muted mt-1">Téléchargez une nouvelle image pour la remplacer</div>
          </div>
        </div>
        @endif

        <div class="form-row form-row-3">
          <div class="form-group">
            <label class="form-label">Nom <span class="required">*</span></label>
            <input type="text" name="nom" class="form-control"
                   value="{{ old('nom', $employe->nom) }}" required>
          </div>
          <div class="form-group">
            <label class="form-label">Postnom</label>
            <input type="text" name="postnom" class="form-control"
                   value="{{ old('postnom', $employe->postnom) }}">
          </div>
          <div class="form-group">
            <label class="form-label">Prénom <span class="required">*</span></label>
            <input type="text" name="prenom" class="form-control"
                   value="{{ old('prenom', $employe->prenom) }}" required>
          </div>
        </div>

        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">Date de naissance <span class="required">*</span></label>
            <input type="date" name="date_naissance" class="form-control"
                   value="{{ old('date_naissance', $employe->date_naissance->format('Y-m-d')) }}" required>
          </div>
          <div class="form-group">
            <label class="form-label">Nouvelle photo</label>
            <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
            <div class="text-sm text-muted mt-1">Laisser vide pour conserver l'actuelle</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Informations professionnelles --}}
    <div class="card mb-4">
      <div class="card-header"><div class="card-title">Informations professionnelles</div></div>
      <div class="card-body">
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">Fonction <span class="required">*</span></label>
            <input type="text" name="fonction" class="form-control"
                   value="{{ old('fonction', $employe->fonction) }}" required>
          </div>
          <div class="form-group">
            <label class="form-label">Année d'engagement <span class="required">*</span></label>
            <input type="number" name="annee_engagement" class="form-control"
                   min="1990" max="{{ date('Y') }}"
                   value="{{ old('annee_engagement', $employe->annee_engagement) }}" required>
          </div>
        </div>
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">Salaire de base ($) <span class="required">*</span></label>
            <input type="number" name="salaire_base" class="form-control"
                   min="0" step="0.01"
                   value="{{ old('salaire_base', $employe->salaire_base) }}" required>
          </div>
          <div class="form-group">
            <label class="form-label">Statut</label>
            <select name="statut" class="form-control">
              @foreach(['actif'=>'Actif','suspendu'=>'Suspendu','retraite'=>'Retraité'] as $val => $label)
                <option value="{{ $val }}" {{ old('statut',$employe->statut)===$val?'selected':'' }}>
                  {{ $label }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:8px">
      <a href="{{ route('admin.employes.show', $employe) }}" class="btn">Annuler</a>
      <button type="submit" class="btn btn-primary">
        <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2">
          <path d="M13 3L6 12l-3-3"/>
        </svg>
        Enregistrer les modifications
      </button>
    </div>
  </form>
</div>
@endsection
