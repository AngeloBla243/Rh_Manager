{{-- resources/views/admin/contrats/edit.blade.php --}}
@extends('layouts.admin')
@section('title', 'Modifier contrat')
@section('page-title', 'Modifier le contrat')
@section('page-sub', $contrat->type . ' — ' . $contrat->employe->nom_complet)

@section('topbar-actions')
    <a href="{{ route('admin.contrats.show', $contrat) }}" class="btn">← Retour</a>
@endsection

@section('content')
    <div style="max-width:720px">
        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                @foreach ($errors->all() as $e)
                    <div>• {{ $e }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.contrats.update', $contrat) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="card mb-4">
                <div class="card-header">
                    <div class="card-title">Informations du contrat</div>
                    <span class="badge badge-gray">{{ $contrat->employe->nom_complet }}</span>
                </div>
                <div class="card-body">
                    <div class="form-row form-row-2">
                        <div class="form-group">
                            <label class="form-label">Type de contrat <span class="required">*</span></label>
                            <select name="type" class="form-control" required id="type-select"
                                onchange="toggleDateFin(this.value)">
                                @foreach (['CDI', 'CDD', 'Stage', 'Interim', 'Freelance', 'Autre'] as $t)
                                    <option value="{{ $t }}"
                                        {{ old('type', $contrat->type) === $t ? 'selected' : '' }}>
                                        {{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Statut <span class="required">*</span></label>
                            <select name="statut" class="form-control" required>
                                @foreach (['actif' => 'En vigueur', 'suspendu' => 'Suspendu', 'expire' => 'Expiré', 'resilie' => 'Résilié', 'renouvele' => 'Renouvelé'] as $val => $label)
                                    <option value="{{ $val }}"
                                        {{ old('statut', $contrat->statut) === $val ? 'selected' : '' }}>{{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row form-row-2">
                        <div class="form-group">
                            <label class="form-label">Date de début <span class="required">*</span></label>
                            <input type="date" name="date_debut" class="form-control" required
                                value="{{ old('date_debut', $contrat->date_debut->format('Y-m-d')) }}">
                        </div>
                        <div class="form-group" id="zone-date-fin">
                            <label class="form-label">Date de fin <small id="label-fin-hint"
                                    class="text-muted"></small></label>
                            <input type="date" name="date_fin" class="form-control" id="date-fin-input"
                                value="{{ old('date_fin', $contrat->date_fin?->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <div class="form-row form-row-2">
                        <div class="form-group">
                            <label class="form-label">Poste occupé</label>
                            <input type="text" name="poste" class="form-control"
                                value="{{ old('poste', $contrat->poste) }}" placeholder="Ex : Responsable comptable">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Salaire contractuel ($)</label>
                            <input type="number" name="salaire_contractuel" class="form-control" min="0"
                                step="0.01" value="{{ old('salaire_contractuel', $contrat->salaire_contractuel) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Numéro de référence</label>
                        <input type="text" name="numero_contrat" class="form-control"
                            value="{{ old('numero_contrat', $contrat->numero_contrat) }}" placeholder="CTR-2025-001">
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <div class="card-title">Période d'essai</div>
                </div>
                <div class="card-body">
                    <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;margin-bottom:12px">
                        <input type="checkbox" name="periode_essai" value="1"
                            {{ old('periode_essai', $contrat->periode_essai) ? 'checked' : '' }}
                            onchange="document.getElementById('zone-essai').style.display=this.checked?'':'none'"
                            style="accent-color:var(--ink)">
                        Ce contrat inclut une période d'essai
                    </label>
                    <div id="zone-essai" style="display:{{ old('periode_essai', $contrat->periode_essai) ? '' : 'none' }}">
                        <div class="form-group">
                            <label class="form-label">Fin de la période d'essai</label>
                            <input type="date" name="fin_periode_essai" class="form-control"
                                value="{{ old('fin_periode_essai', $contrat->fin_periode_essai?->format('Y-m-d')) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <div class="card-title">Document & Notes</div>
                </div>
                <div class="card-body">
                    @if ($contrat->a_document)
                        <div
                            style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:var(--bg2);border-radius:var(--r);margin-bottom:12px">
                            <span class="text-sm">📄 Document actuel :
                                <strong>contrat-{{ $contrat->employe->matricule }}.pdf</strong></span>
                            <a href="{{ route('admin.contrats.download', $contrat) }}" class="btn btn-sm">Télécharger</a>
                        </div>
                    @endif
                    <div class="form-group">
                        <label
                            class="form-label">{{ $contrat->a_document ? 'Remplacer le document' : 'Ajouter un document signé' }}</label>
                        <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <div class="text-xs text-muted mt-1">PDF recommandé — max 10 Mo — laisser vide pour conserver
                            l'actuel</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description / Notes</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $contrat->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:8px">
                <a href="{{ route('admin.contrats.show', $contrat) }}" class="btn">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor"
                        stroke-width="2.2">
                        <path d="M13 3L6 12l-3-3" />
                    </svg>
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function toggleDateFin(type) {
                const required = ['CDD', 'Stage', 'Interim'].includes(type);
                const input = document.getElementById('date-fin-input');
                const hint = document.getElementById('label-fin-hint');
                input.required = required;
                hint.textContent = required ? '(obligatoire)' : '(optionnel)';
            }
            toggleDateFin(document.getElementById('type-select').value);
        </script>
    @endpush
@endsection
