{{-- resources/views/admin/employes/show.blade.php --}}
@extends('layouts.admin')
@section('title', $employe->nom_complet)
@section('page-title', $employe->nom_complet)
@section('page-sub', 'Fiche employé — ' . $employe->matricule)

@section('topbar-actions')
  <a href="{{ route('admin.employes.index') }}" class="btn">← Retour</a>
  <a href="{{ route('admin.pdf.carte-service', $employe) }}" class="btn" target="_blank">
    <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
      <rect x="1" y="3.5" width="14" height="9" rx="1.5"/><path d="M1 7h14M4 10.5h3"/>
    </svg>
    Carte de service
  </a>
  <a href="{{ route('admin.employes.edit', $employe) }}" class="btn btn-primary">
    <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M11.5 2.5l2 2L5 13H3v-2z"/>
    </svg>
    Modifier
  </a>
@endsection

@section('content')
<div class="grid grid-3-1 gap-4">

  {{-- ── Colonne principale ───────────────────────────── --}}
  <div>
    {{-- Infos générales --}}
    <div class="card mb-4">
      <div class="card-body">
        <div style="display:flex;gap:16px;align-items:flex-start">
          <div class="avatar avatar-xl">
            @if($employe->photo)
              <img src="{{ asset('storage/' . $employe->photo) }}" alt="">
            @else
              {{ mb_strtoupper(mb_substr($employe->nom,0,1).mb_substr($employe->prenom,0,1)) }}
            @endif
          </div>
          <div style="flex:1">
            <div style="font-size:20px;font-weight:700;letter-spacing:-.4px">{{ $employe->nom_complet }}</div>
            <div style="color:var(--ink3);margin-top:3px">{{ $employe->fonction }}</div>
            <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
              @if($employe->statut === 'actif')
                <span class="badge badge-green">Actif</span>
              @else
                <span class="badge badge-amber">{{ ucfirst($employe->statut) }}</span>
              @endif
              <span class="tag">{{ $employe->matricule }}</span>
              <span class="tag">{{ $employe->anciennete }} an{{ $employe->anciennete > 1 ? 's' : '' }} d'ancienneté</span>
            </div>
          </div>
        </div>

        <hr class="divider">

        <div class="info-list">
          <div class="info-item">
            <span class="info-item-label">Date de naissance</span>
            <span class="info-item-val">{{ $employe->date_naissance->locale('fr')->isoFormat('D MMMM YYYY') }}</span>
          </div>
          <div class="info-item">
            <span class="info-item-label">Année d'engagement</span>
            <span class="info-item-val font-mono">{{ $employe->annee_engagement }}</span>
          </div>
          <div class="info-item">
            <span class="info-item-label">Salaire de base</span>
            <span class="info-item-val font-mono" style="color:var(--emerald)">{{ number_format($employe->salaire_base,2,',',' ') }} $</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Statistiques du mois --}}
    <div class="card mb-4">
      <div class="card-header">
        <div class="card-title">Ce mois — {{ ucfirst(\Carbon\Carbon::now()->locale('fr')->isoFormat('MMMM YYYY')) }}</div>
      </div>
      <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        @php
          $dernierSalaire = $employe->salaires()->where('mois', now()->month)->where('annee', now()->year)->first();
        @endphp
        <div class="stat-card" style="border:none;background:var(--bg);padding:12px">
          <div class="stat-label">Jours travaillés</div>
          <div class="stat-value" style="font-size:22px">{{ $dernierSalaire?->jours_travailles ?? '—' }}</div>
          <div class="stat-sub">/ {{ $dernierSalaire?->jours_ouvres ?? '—' }} ouvrés</div>
        </div>
        <div class="stat-card" style="border:none;background:var(--bg);padding:12px">
          <div class="stat-label">Taux présence</div>
          <div class="stat-value" style="font-size:22px">{{ $dernierSalaire ? $dernierSalaire->taux_presence . '%' : '—' }}</div>
          <div class="stat-sub">{{ $dernierSalaire?->nb_absences ?? 0 }} absence(s)</div>
        </div>
        <div class="stat-card" style="border:none;background:var(--bg);padding:12px">
          <div class="stat-label">Pénalités</div>
          <div class="stat-value" style="font-size:20px;color:var(--crimson)">
            {{ $dernierSalaire ? '- ' . number_format($dernierSalaire->total_penalites,2,',',' ') . ' $' : '—' }}
          </div>
        </div>
        <div class="stat-card" style="border:none;background:var(--bg);padding:12px">
          <div class="stat-label">Net à payer</div>
          <div class="stat-value" style="font-size:20px;color:var(--emerald)">
            {{ $dernierSalaire ? number_format($dernierSalaire->salaire_net,2,',',' ') . ' $' : '—' }}
          </div>
        </div>
      </div>
    </div>

    {{-- Documents --}}
    <div class="card mb-4">
      <div class="card-header">
        <div class="card-title">Documents administratifs</div>
        <button class="btn btn-sm btn-primary" onclick="document.getElementById('modal-doc').style.display='flex'">
          + Ajouter
        </button>
      </div>
      @if($employe->documents->count())
        <div class="table-wrap">
          <table>
            <thead><tr><th>Type</th><th>Fichier</th><th>Ajouté le</th><th></th></tr></thead>
            <tbody>
              @foreach($employe->documents as $doc)
                <tr>
                  <td><strong>{{ $doc->type_document }}</strong></td>
                  <td>
                    <div style="display:flex;align-items:center;gap:6px;font-size:12px">
                      <span style="background:var(--bg2);padding:2px 6px;border-radius:4px;font-family:var(--mono);text-transform:uppercase;font-size:10px">{{ $doc->extension }}</span>
                      {{ $doc->nom_fichier }}
                    </div>
                  </td>
                  <td class="text-sm text-muted">{{ $doc->created_at->format('d/m/Y') }}</td>
                  <td>
                    <a href="{{ route('admin.employes.documents.download', $doc) }}" class="btn btn-sm">↓</a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="empty" style="padding:28px">
          <div class="empty-title">Aucun document</div>
          <div class="empty-sub">Ajoutez les documents administratifs de l'employé.</div>
        </div>
      @endif
    </div>
  </div>

  {{-- ── Colonne latérale ──────────────────────────────── --}}
  <div>
    {{-- Empreinte digitale --}}
    <div class="card mb-4">
      <div class="card-header"><div class="card-title">Empreinte digitale</div></div>
      <div class="card-body" style="text-align:center">
        <div class="fp-container" onclick="lancerEnrolement()" style="margin-bottom:12px">
          <svg width="52" height="62" viewBox="0 0 60 70" fill="none" stroke="var(--ink3)" stroke-width="1.2">
            <path d="M30 5C18 5 8 14 8 26c0 12 6 26 14 36" stroke-width="1.5"/>
            <path d="M30 5c12 0 22 9 22 21 0 12-6 26-14 36" stroke-width="1.5"/>
            <path d="M18 20c0-7 5.4-12 12-12s12 5 12 12c0 10-6 22-12 30C24 42 18 30 18 20"/>
            <path d="M22 28c0-5 3.5-8 8-8s8 3 8 8c0 6-3 12-8 17"/>
            <path d="M26 34c0-3 1.5-5 4-5s4 2 4 5c0 3-2 6-4 8"/>
          </svg>
          <div class="fp-scan-line"></div>
        </div>

        @if($employe->empreinte_id)
          <div class="fp-status success">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" style="display:inline;vertical-align:middle">
              <circle cx="8" cy="8" r="6.5"/><path d="M5 8l2 2 4-4"/>
            </svg>
            Empreinte enregistrée (ID: {{ $employe->empreinte_id }})
          </div>
          <button class="btn btn-sm btn-danger mt-2" onclick="supprimerEmpreinte()">Supprimer empreinte</button>
        @else
          <div class="fp-status">Cliquez pour enregistrer l'empreinte</div>
          <div class="text-xs text-muted mt-2">L'appareil biométrique doit être connecté</div>
        @endif
      </div>
    </div>

    {{-- Historique présences récentes --}}
    <div class="card mb-4">
      <div class="card-header">
        <div class="card-title">Présences récentes</div>
        <a href="{{ route('admin.presences.index', ['employe' => $employe->id]) }}" class="btn btn-sm">Tout voir</a>
      </div>
      <div style="padding:0">
        @forelse($employe->presences()->latest('date')->take(7)->get() as $p)
          <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 16px;border-bottom:1px solid var(--border)">
            <div>
              <div style="font-size:12.5px;font-weight:500">{{ $p->date->locale('fr')->isoFormat('ddd D MMM') }}</div>
              @if($p->heure_entree)
                <div class="text-xs text-muted font-mono">
                  {{ \Carbon\Carbon::parse($p->heure_entree)->format('H:i') }}
                  @if($p->heure_sortie)→ {{ \Carbon\Carbon::parse($p->heure_sortie)->format('H:i') }}@endif
                </div>
              @endif
            </div>
            @if(!$p->heure_entree)
              <span class="badge badge-red">Absent</span>
            @elseif($p->est_retard)
              <span class="badge badge-amber">Retard</span>
            @elseif($p->est_valide)
              <span class="badge badge-green">✓</span>
            @else
              <span class="badge badge-blue">En cours</span>
            @endif
          </div>
        @empty
          <div class="empty" style="padding:20px">
            <div class="empty-sub">Aucun pointage enregistré</div>
          </div>
        @endforelse
      </div>
    </div>

    {{-- Actions rapides --}}
    <div class="card">
      <div class="card-header"><div class="card-title">Actions</div></div>
      <div class="card-body" style="display:flex;flex-direction:column;gap:8px">
        @if($dernierSalaire)
          <a href="{{ route('admin.pdf.fiche-individuelle', $dernierSalaire) }}" class="btn w-full" style="justify-content:center" target="_blank">
            📄 Fiche de paie du mois
          </a>
        @endif
        <a href="{{ route('admin.pdf.carte-service', $employe) }}" class="btn w-full" style="justify-content:center" target="_blank">
          🪪 Imprimer carte de service
        </a>
        <form method="POST" action="{{ route('admin.employes.destroy', $employe) }}">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-danger w-full" style="justify-content:center"
            onclick="return confirm('Archiver cet employé ?')">
            Archiver l'employé
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- Modal : ajouter document --}}
<div class="modal-overlay" id="modal-doc" style="display:none">
  <div class="modal modal-sm">
    <div class="modal-header">
      <span class="modal-title">Ajouter un document</span>
      <button class="modal-close" onclick="document.getElementById('modal-doc').style.display='none'">✕</button>
    </div>
    <form method="POST" action="{{ route('admin.employes.documents.store', $employe) }}" enctype="multipart/form-data">
      @csrf
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Type de document <span class="required">*</span></label>
          <select name="type_document" class="form-control" required>
            @foreach(json_decode(\App\Models\Parametre::valeur('types_documents', '[]'), true) as $type)
              <option value="{{ $type }}">{{ $type }}</option>
            @endforeach
            <option value="autre">Autre</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Fichier <span class="required">*</span></label>
          <input type="file" name="fichier" class="form-control" required
                 accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
          <div class="text-xs text-muted mt-1">PDF, image, Word — max 10 Mo</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" onclick="document.getElementById('modal-doc').style.display='none'">Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function lancerEnrolement() {
  if (confirm("Lancer l'enrôlement de l'empreinte sur l'appareil biométrique ?")) {
    fetch('/admin/biometrique/enroler/{{ $employe->id }}', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
    }).then(r => r.json()).then(d => alert(d.message));
  }
}
function supprimerEmpreinte() {
  if (confirm("Supprimer l'empreinte de l'appareil ?")) {
    fetch('/admin/biometrique/supprimer/{{ $employe->id }}', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
    }).then(r => r.json()).then(d => { alert(d.message); location.reload(); });
  }
}
</script>
@endpush
@endsection
