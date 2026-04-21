{{-- resources/views/admin/conges/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Congés & Jours fériés')
@section('page-title', 'Congés & Jours fériés')
@section('page-sub', 'Gestion des absences programmées et jours non travaillés')

@section('topbar-actions')
  <button class="btn btn-primary" onclick="document.getElementById('modal-conge').style.display='flex'">
    + Nouvelle demande
  </button>
@endsection

@section('content')
<div class="grid grid-2 mb-4">
  {{-- Congés --}}
  <div class="card">
    <div class="card-header"><div class="card-title">Demandes de congé</div></div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Employé</th><th>Période</th><th>Type</th><th>Statut</th><th></th></tr></thead>
        <tbody>
          @forelse($conges as $c)
            <tr>
              <td>
                <div class="emp-cell">
                  <div class="avatar">{{ mb_strtoupper(mb_substr($c->employe->nom,0,1).mb_substr($c->employe->prenom,0,1)) }}</div>
                  <div>
                    <div class="emp-name">{{ $c->employe->nom }} {{ $c->employe->prenom }}</div>
                    <div class="emp-sub">{{ $c->nombre_jours }} jour(s)</div>
                  </div>
                </div>
              </td>
              <td class="text-sm">
                {{ $c->date_debut->format('d/m') }} → {{ $c->date_fin->format('d/m/Y') }}
              </td>
              <td><span class="badge badge-blue">{{ ucfirst(str_replace('_',' ',$c->type)) }}</span></td>
              <td>
                @if($c->statut==='approuve') <span class="badge badge-green">Approuvé</span>
                @elseif($c->statut==='refuse') <span class="badge badge-red">Refusé</span>
                @else <span class="badge badge-amber">En attente</span>
                @endif
              </td>
              <td>
                @if($c->statut==='en_attente')
                  <div class="table-actions">
                    <form method="POST" action="{{ route('admin.conges.approuver',$c) }}">@csrf<button class="btn btn-sm btn-emerald">✓</button></form>
                    <form method="POST" action="{{ route('admin.conges.refuser',$c) }}">@csrf<button class="btn btn-sm btn-danger">✕</button></form>
                  </div>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="5"><div class="empty" style="padding:24px"><div class="empty-title">Aucune demande</div></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Jours fériés --}}
  <div class="card">
    <div class="card-header">
      <div class="card-title">Jours fériés {{ now()->year }}</div>
      <button class="btn btn-sm btn-primary" onclick="document.getElementById('modal-ferie').style.display='flex'">+ Ajouter</button>
    </div>
    <div style="padding:0">
      @forelse($joursFeries as $jf)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-bottom:1px solid var(--border)">
          <div>
            <span class="font-mono text-sm" style="color:var(--cobalt);font-weight:600">{{ $jf->date->format('d MMM') }}</span>
            <span style="margin-left:10px;font-size:13px">{{ $jf->libelle }}</span>
          </div>
          <form method="POST" action="{{ route('admin.jours-feries.destroy',$jf) }}">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Supprimer ce jour férié ?')">✕</button>
          </form>
        </div>
      @empty
        <div class="empty" style="padding:24px"><div class="empty-title">Aucun jour férié configuré</div></div>
      @endforelse
    </div>

    <div class="card-footer">
      <div style="display:flex;gap:8px;font-size:12px;color:var(--ink3)">
        <div style="display:flex;align-items:center;gap:5px">
          <input type="checkbox" checked disabled style="accent-color:var(--ink)"> Samedi non travaillé
        </div>
        <div style="display:flex;align-items:center;gap:5px">
          <input type="checkbox" checked disabled style="accent-color:var(--ink)"> Dimanche non travaillé
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Modal congé --}}
<div class="modal-overlay" id="modal-conge" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Nouvelle demande de congé</span>
      <button class="modal-close" onclick="document.getElementById('modal-conge').style.display='none'">✕</button>
    </div>
    <form method="POST" action="{{ route('admin.conges.store') }}">
      @csrf
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Employé <span class="required">*</span></label>
          <select name="employe_id" class="form-control" required>
            <option value="">— Sélectionner —</option>
            @foreach($employes as $e)
              <option value="{{ $e->id }}">{{ $e->nom }} {{ $e->prenom }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">Date de début <span class="required">*</span></label>
            <input type="date" name="date_debut" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Date de fin <span class="required">*</span></label>
            <input type="date" name="date_fin" class="form-control" required>
          </div>
        </div>
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">Type de congé <span class="required">*</span></label>
            <select name="type" class="form-control" required>
              <option value="annuel">Congé annuel</option>
              <option value="maladie">Congé maladie</option>
              <option value="maternite">Congé maternité</option>
              <option value="sans_solde">Congé sans solde</option>
              <option value="exceptionnel">Congé exceptionnel</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Statut initial</label>
            <select name="statut" class="form-control">
              <option value="en_attente">En attente</option>
              <option value="approuve">Approuvé directement</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Motif</label>
          <textarea name="motif" class="form-control" rows="2" placeholder="Raison de la demande…"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" onclick="document.getElementById('modal-conge').style.display='none'">Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer la demande</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal jour férié --}}
<div class="modal-overlay" id="modal-ferie" style="display:none">
  <div class="modal modal-sm">
    <div class="modal-header">
      <span class="modal-title">Ajouter un jour férié</span>
      <button class="modal-close" onclick="document.getElementById('modal-ferie').style.display='none'">✕</button>
    </div>
    <form method="POST" action="{{ route('admin.jours-feries.store') }}">
      @csrf
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Date <span class="required">*</span></label>
          <input type="date" name="date" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Libellé <span class="required">*</span></label>
          <input type="text" name="libelle" class="form-control" required placeholder="Ex : Fête nationale">
        </div>
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
          <input type="checkbox" name="recurrent" value="1" checked style="accent-color:var(--ink)">
          Récurrent chaque année
        </label>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" onclick="document.getElementById('modal-ferie').style.display='none'">Annuler</button>
        <button type="submit" class="btn btn-primary">Ajouter</button>
      </div>
    </form>
  </div>
</div>
@endsection
