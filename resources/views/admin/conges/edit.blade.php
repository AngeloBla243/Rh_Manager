@extends('layouts.admin')
@section('title', 'Modifier congé')
@section('page-title', 'Modifier la demande de congé')

@section('topbar-actions')
    <a href="{{ route('admin.conges.show', $conge) }}" class="btn">← Retour</a>
@endsection

@section('content')
    <div style="max-width:620px">
        <form method="POST" action="{{ route('admin.conges.update', $conge) }}">
            @csrf @method('PUT')
            <div class="card mb-4">
                <div class="card-header">
                    <div class="card-title">Informations du congé</div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Employé <span class="required">*</span></label>
                        <select name="employe_id" class="form-control" required onchange="updateSolde(this.value)">
                            <option value="">— Sélectionner un employé —</option>
                            @foreach ($employes as $e)
                                <option value="{{ $e->id }}">{{ $e->nom }} {{ $e->prenom }} —
                                    {{ $e->fonction }}</option>
                            @endforeach
                        </select>
                        <div id="solde-info" class="text-sm text-muted mt-1" style="display:none">
                            Solde congés annuels restants : <strong id="solde-val"></strong>
                        </div>
                    </div>

                    <div class="form-row form-row-2">
                        <div class="form-group">
                            <label class="form-label">Date de début <span class="required">*</span></label>
                            <input type="date" name="date_debut" id="date-debut" class="form-control"
                                value="{{ old('date_debut') }}" required onchange="calculerJours()">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date de fin <span class="required">*</span></label>
                            <input type="date" name="date_fin" id="date-fin" class="form-control"
                                value="{{ old('date_fin') }}" required onchange="calculerJours()">
                        </div>
                    </div>

                    <div id="nb-jours-info"
                        style="display:none;padding:10px 14px;background:var(--cobalt-bg);border-radius:var(--r);margin-bottom:14px;font-size:13px;color:var(--cobalt)">
                        Durée estimée : <strong id="nb-jours-val"></strong> jours ouvrés
                    </div>

                    <div class="form-row form-row-2">
                        <div class="form-group">
                            <label class="form-label">Type de congé <span class="required">*</span></label>
                            <select name="type" class="form-control" required>
                                <option value="annuel" {{ old('type') === 'annuel' ? 'selected' : '' }}>Congé annuel
                                </option>
                                <option value="maladie" {{ old('type') === 'maladie' ? 'selected' : '' }}>Congé maladie
                                </option>
                                <option value="maternite" {{ old('type') === 'maternite' ? 'selected' : '' }}>Congé
                                    maternité
                                </option>
                                <option value="sans_solde" {{ old('type') === 'sans_solde' ? 'selected' : '' }}>Congé sans
                                    solde
                                </option>
                                <option value="exceptionnel" {{ old('type') === 'exceptionnel' ? 'selected' : '' }}>Congé
                                    exceptionnel</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Statut initial</label>
                            <select name="statut" class="form-control">
                                <option value="en_attente">En attente de validation</option>
                                <option value="approuve">Approuvé directement</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Motif</label>
                        <textarea name="motif" class="form-control" rows="2" placeholder="Raison de la demande…">{{ old('motif') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Document justificatif</label>
                        <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="text-xs text-muted mt-1">Optionnel — PDF, image ou Word — max 5 Mo</div>
                    </div>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:8px">
                <a href="{{ route('admin.conges.index') }}" class="btn">Annuler</a>
                <button type="submit" class="btn btn-primary">Enregistrer la demande</button>
            </div>
        </form>
    </div>
@endsection
