{{-- resources/views/admin/conges/show.blade.php --}}
@extends('layouts.admin')
@section('title', 'Détail congé')
@section('page-title', 'Détail du congé')
@section('page-sub', $conge->employe->nom_complet . ' — ' . $conge->periode)

@section('topbar-actions')
    <a href="{{ route('admin.conges.index') }}" class="btn">← Retour</a>
    @if ($conge->statut === 'en_attente')
        <form method="POST" action="{{ route('admin.conges.approuver', $conge) }}" style="display:inline">
            @csrf
            <button type="submit" class="btn btn-emerald">✓ Approuver</button>
        </form>
        <button class="btn btn-danger" onclick="document.getElementById('modal-refus').style.display='flex'">✕
            Refuser</button>
    @endif
@endsection

@section('content')
    <div style="max-width:700px">
        <div class="card mb-4">
            <div class="card-body">
                <div style="display:flex;gap:16px;align-items:flex-start">
                    <div class="avatar avatar-lg">{{ $conge->employe->initiales }}</div>
                    <div style="flex:1">
                        <div style="font-size:18px;font-weight:700">{{ $conge->employe->nom_complet }}</div>
                        <div class="text-muted mt-1">{{ $conge->employe->fonction }}</div>
                        <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
                            <span class="badge badge-blue">{{ $conge->type_libelle }}</span>
                            <span class="badge badge-{{ $conge->statut_couleur }}">{{ $conge->statut_libelle }}</span>
                            @if ($conge->est_en_cours)
                                <span class="badge badge-green">En cours aujourd'hui</span>
                            @endif
                        </div>
                    </div>
                </div>

                <hr class="divider">

                <div class="info-list">
                    <div class="info-item">
                        <span class="info-item-label">Période</span>
                        <span class="info-item-val">{{ $conge->date_debut->locale('fr')->isoFormat('D MMMM YYYY') }} →
                            {{ $conge->date_fin->locale('fr')->isoFormat('D MMMM YYYY') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">Nombre de jours ouvrés</span>
                        <span class="info-item-val font-mono">{{ $conge->nombre_jours }} jour(s)</span>
                    </div>
                    <div class="info-item">
                        <span class="info-item-label">Solde restant (annuel)</span>
                        <span class="info-item-val font-mono">{{ $conge->employe->soldeConges() }} jour(s) restants</span>
                    </div>
                    @if ($conge->motif)
                        <div class="info-item">
                            <span class="info-item-label">Motif</span>
                            <span class="info-item-val">{{ $conge->motif }}</span>
                        </div>
                    @endif
                    @if ($conge->commentaire_admin)
                        <div class="info-item">
                            <span class="info-item-label">Commentaire admin</span>
                            <span class="info-item-val">{{ $conge->commentaire_admin }}</span>
                        </div>
                    @endif
                    @if ($conge->a_document)
                        <div class="info-item">
                            <span class="info-item-label">Document joint</span>
                            <a href="{{ Storage::url($conge->document) }}" target="_blank" class="btn btn-sm">
                                📄 Télécharger
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if ($conge->statut === 'approuve')
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Annuler ce congé</div>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-3">
                        L'annulation supprimera les absences de type « congé » créées pour cette période.
                    </div>
                    <form method="POST" action="{{ route('admin.conges.destroy', $conge) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Annuler ce congé ?')">
                            Annuler le congé
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal refus --}}
    <div class="modal-overlay" id="modal-refus" style="display:none">
        <div class="modal modal-sm">
            <div class="modal-header">
                <span class="modal-title">Refuser le congé</span>
                <button class="modal-close" onclick="document.getElementById('modal-refus').style.display='none'">✕</button>
            </div>
            <form method="POST" action="{{ route('admin.conges.refuser', $conge) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Motif du refus</label>
                        <textarea name="commentaire" class="form-control" rows="3" placeholder="Expliquez la raison du refus…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn"
                        onclick="document.getElementById('modal-refus').style.display='none'">Annuler</button>
                    <button type="submit" class="btn btn-danger">Confirmer le refus</button>
                </div>
            </form>
        </div>
    </div>
@endsection
