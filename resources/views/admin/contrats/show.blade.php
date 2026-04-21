{{-- resources/views/admin/contrats/show.blade.php --}}
@extends('layouts.admin')
@section('title', 'Contrat — ' . $contrat->employe->nom_complet)
@section('page-title', 'Détail du contrat')
@section('page-sub', $contrat->type_libelle . ' — ' . $contrat->employe->nom_complet)

@section('topbar-actions')
  <a href="{{ route('admin.contrats.index') }}" class="btn">← Retour</a>
  @if($contrat->a_document)
    <a href="{{ route('admin.contrats.download', $contrat) }}" class="btn">
      📄 Télécharger le contrat
    </a>
  @endif
  <a href="{{ route('admin.contrats.edit', $contrat) }}" class="btn btn-primary">✎ Modifier</a>
@endsection

@section('content')
<div class="grid grid-3-1 gap-4">

  <div>
    {{-- En-tête contrat --}}
    <div class="card mb-4">
      <div class="card-body">
        <div style="display:flex;gap:16px;align-items:flex-start">
          <div class="avatar avatar-xl">{{ $contrat->employe->initiales }}</div>
          <div style="flex:1">
            <div style="font-size:20px;font-weight:700;letter-spacing:-.4px">
              {{ $contrat->employe->nom_complet }}
            </div>
            <div class="text-muted mt-1">{{ $contrat->poste ?? $contrat->employe->fonction }}</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
              <span class="badge {{ match($contrat->type) {
                'CDI'   => 'badge-green',
                'CDD'   => 'badge-blue',
                'Stage' => 'badge-amber',
                default => 'badge-gray'
              } }}">{{ $contrat->type }}</span>
              <span class="badge badge-{{ $contrat->statut_couleur }}">{{ $contrat->statut_libelle }}</span>
              @if($contrat->en_periode_essai)
                <span class="badge badge-amber">Période d'essai</span>
              @endif
              @if($contrat->expire_bientot)
                <span class="badge badge-amber">⚠ Expire dans {{ $contrat->jours_restants }} jours</span>
              @endif
              @if($contrat->renouvellDepuis)
                <span class="badge badge-blue">Renouvellement</span>
              @endif
            </div>
          </div>
        </div>

        <hr class="divider">

        <div class="info-list">
          <div class="info-item">
            <span class="info-item-label">Type de contrat</span>
            <span class="info-item-val">{{ $contrat->type_libelle }}</span>
          </div>
          <div class="info-item">
            <span class="info-item-label">Date de début</span>
            <span class="info-item-val font-mono">{{ $contrat->date_debut->locale('fr')->isoFormat('D MMMM YYYY') }}</span>
          </div>
          <div class="info-item">
            <span class="info-item-label">Date de fin</span>
            <span class="info-item-val font-mono">
              {{ $contrat->date_fin ? $contrat->date_fin->locale('fr')->isoFormat('D MMMM YYYY') : 'Indéterminée' }}
            </span>
          </div>
          <div class="info-item">
            <span class="info-item-label">Durée</span>
            <span class="info-item-val">{{ $contrat->duree_formatee }}</span>
          </div>
          @if($contrat->numero_contrat)
            <div class="info-item">
              <span class="info-item-label">Référence</span>
              <span class="info-item-val font-mono">{{ $contrat->numero_contrat }}</span>
            </div>
          @endif
          @if($contrat->salaire_contractuel)
            <div class="info-item">
              <span class="info-item-label">Salaire contractuel</span>
              <span class="info-item-val font-mono" style="color:var(--emerald)">
                {{ number_format($contrat->salaire_contractuel, 2, ',', ' ') }} $
              </span>
            </div>
          @endif
          @if($contrat->periode_essai && $contrat->fin_periode_essai)
            <div class="info-item">
              <span class="info-item-label">Fin période d'essai</span>
              <span class="info-item-val font-mono">{{ $contrat->fin_periode_essai->format('d/m/Y') }}</span>
            </div>
          @endif
        </div>

        @if($contrat->description)
          <hr class="divider">
          <div>
            <div class="section-title mb-2" style="font-size:11px">Notes contractuelles</div>
            <div style="font-size:13px;color:var(--ink2);line-height:1.6;white-space:pre-line">{{ $contrat->description }}</div>
          </div>
        @endif
      </div>
    </div>

    {{-- Historique renouvellements --}}
    @if($contrat->renouvellDepuis || $contrat->renouvellements->count() > 0)
      <div class="card mb-4">
        <div class="card-header"><div class="card-title">Historique des renouvellements</div></div>
        <div style="padding:0">
          @if($contrat->renouvellDepuis)
            <div style="padding:10px 16px;border-bottom:1px solid var(--border);font-size:12.5px">
              <span class="text-muted">Contrat d'origine :</span>
              <a href="{{ route('admin.contrats.show', $contrat->renouvellDepuis) }}" class="btn btn-sm" style="margin-left:8px">
                {{ $contrat->renouvellDepuis->type }} — {{ $contrat->renouvellDepuis->date_debut->format('d/m/Y') }}
              </a>
            </div>
          @endif
          @foreach($contrat->renouvellements as $r)
            <div style="padding:10px 16px;border-bottom:1px solid var(--border);font-size:12.5px">
              <span class="text-muted">Renouvelé vers :</span>
              <a href="{{ route('admin.contrats.show', $r) }}" class="btn btn-sm" style="margin-left:8px">
                {{ $r->type }} — jusqu'au {{ $r->date_fin?->format('d/m/Y') ?? 'indéterminé' }}
              </a>
            </div>
          @endforeach
        </div>
      </div>
    @endif
  </div>

  {{-- Colonne latérale --}}
  <div>
    <div class="card mb-4">
      <div class="card-header"><div class="card-title">Actions</div></div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:8px">

        @if($contrat->statut === 'actif' && in_array($contrat->type, ['CDD','Stage','Interim']))
          <button class="btn w-full" style="justify-content:center"
                  onclick="document.getElementById('modal-renouveler').style.display='flex'">
            ↻ Renouveler ce contrat
          </button>
        @endif

        @if($contrat->statut === 'actif')
          <button class="btn btn-danger w-full" style="justify-content:center"
                  onclick="document.getElementById('modal-resilier').style.display='flex'">
            Résilier le contrat
          </button>
        @endif

        <a href="{{ route('admin.employes.show', $contrat->employe) }}" class="btn w-full" style="justify-content:center">
          Voir la fiche employé
        </a>

        <a href="{{ route('admin.contrats.create', ['employe_id' => $contrat->employe_id]) }}"
           class="btn w-full" style="justify-content:center">
          + Nouveau contrat pour cet employé
        </a>

        <form method="POST" action="{{ route('admin.contrats.destroy', $contrat) }}">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-danger w-full" style="justify-content:center"
                  onclick="return confirm('Supprimer définitivement ce contrat ?')">
            Supprimer
          </button>
        </form>
      </div>
    </div>

    {{-- Document --}}
    <div class="card">
      <div class="card-header"><div class="card-title">Document signé</div></div>
      <div class="card-body">
        @if($contrat->a_document)
          <div style="padding:12px;background:var(--emerald-bg);border-radius:var(--r);margin-bottom:10px;text-align:center">
            <div style="font-size:24px;margin-bottom:4px">📄</div>
            <div style="font-size:12px;color:var(--emerald)">Contrat scanné disponible</div>
          </div>
          <a href="{{ route('admin.contrats.download', $contrat) }}" class="btn w-full" style="justify-content:center">
            ↓ Télécharger le PDF
          </a>
        @else
          <div class="empty" style="padding:20px">
            <div class="empty-sub">Aucun document joint</div>
          </div>
          <a href="{{ route('admin.contrats.edit', $contrat) }}" class="btn btn-sm w-full" style="justify-content:center">
            Ajouter un document
          </a>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- Modal résilier --}}
<div class="modal-overlay" id="modal-resilier" style="display:none">
  <div class="modal modal-sm">
    <div class="modal-header">
      <span class="modal-title">Résilier le contrat</span>
      <button class="modal-close" onclick="document.getElementById('modal-resilier').style.display='none'">✕</button>
    </div>
    <form method="POST" action="{{ route('admin.contrats.resilier', $contrat) }}">
      @csrf
      <div class="modal-body">
        <div class="alert alert-danger mb-4">Cette action est irréversible.</div>
        <div class="form-group">
          <label class="form-label">Date de résiliation <span class="required">*</span></label>
          <input type="date" name="date_resiliation" class="form-control" value="{{ now()->toDateString() }}" required>
        </div>
        <div class="form-group">
          <label class="form-label">Motif <span class="required">*</span></label>
          <textarea name="motif_resiliation" class="form-control" rows="3" required
                    placeholder="Ex : Démission, faute grave, fin anticipée…"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" onclick="document.getElementById('modal-resilier').style.display='none'">Annuler</button>
        <button type="submit" class="btn btn-danger">Confirmer la résiliation</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal renouveler --}}
<div class="modal-overlay" id="modal-renouveler" style="display:none">
  <div class="modal modal-sm">
    <div class="modal-header">
      <span class="modal-title">Renouveler le contrat</span>
      <button class="modal-close" onclick="document.getElementById('modal-renouveler').style.display='none'">✕</button>
    </div>
    <form method="POST" action="{{ route('admin.contrats.renouveler', $contrat) }}">
      @csrf
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Nouvelle date de fin <span class="required">*</span></label>
          <input type="date" name="nouvelle_date_fin" class="form-control"
                 min="{{ $contrat->date_fin?->addDay()->toDateString() ?? now()->toDateString() }}" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" onclick="document.getElementById('modal-renouveler').style.display='none'">Annuler</button>
        <button type="submit" class="btn btn-primary">↻ Renouveler</button>
      </div>
    </form>
  </div>
</div>
@endsection
