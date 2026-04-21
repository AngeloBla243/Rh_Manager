{{-- resources/views/admin/parametres/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Paramètres')
@section('page-title', 'Paramètres système')
@section('page-sub', 'Configuration globale de l\'application')

@section('topbar-actions')
  <button form="form-params" type="submit" class="btn btn-primary">
    <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M13 3L6 12l-3-3"/></svg>
    Sauvegarder les modifications
  </button>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.parametres.update') }}" id="form-params">
  @csrf

  <div class="grid grid-2 mb-4">
    {{-- Horaires --}}
    <div class="card">
      <div class="card-header"><div class="card-title">⏰ Horaires de travail</div></div>
      <div class="card-body">
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">Heure d'arrivée normale</label>
            <input type="time" name="heure_arrivee" class="form-control"
                   value="{{ \App\Models\Parametre::valeur('heure_arrivee','08:00') }}">
          </div>
          <div class="form-group">
            <label class="form-label">Limite sans retard</label>
            <input type="time" name="heure_limite_retard" class="form-control"
                   value="{{ \App\Models\Parametre::valeur('heure_limite_retard','08:30') }}">
            <div class="text-xs text-muted mt-1">Après cette heure → retard enregistré</div>
          </div>
        </div>
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">Heure de sortie normale</label>
            <input type="time" name="heure_sortie" class="form-control"
                   value="{{ \App\Models\Parametre::valeur('heure_sortie','17:00') }}">
          </div>
          <div class="form-group">
            <label class="form-label">Jours de congé annuels</label>
            <input type="number" name="conge_jours_par_an" class="form-control"
                   min="1" max="60" value="{{ \App\Models\Parametre::valeur('conge_jours_par_an','21') }}">
          </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;padding:12px;background:var(--bg2);border-radius:var(--r)">
          <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
            <input type="checkbox" name="samedi_non_travaille" value="1" checked style="accent-color:var(--ink)">
            Samedi non travaillé
          </label>
          <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
            <input type="checkbox" name="dimanche_non_travaille" value="1" checked style="accent-color:var(--ink)">
            Dimanche non travaillé
          </label>
        </div>
      </div>
    </div>

    {{-- Pénalités --}}
    <div class="card">
      <div class="card-header"><div class="card-title">⚠️ Pénalités</div></div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">% pénalité par absence non justifiée</label>
          <div style="display:flex;align-items:center;gap:10px">
            <input type="range" name="penalite_absence_pct" min="0" max="50" step="1"
                   value="{{ \App\Models\Parametre::valeur('penalite_absence_pct','5') }}"
                   oninput="document.getElementById('abs-val').textContent=this.value+'%'"
                   style="flex:1;accent-color:var(--ink)">
            <strong id="abs-val" class="font-mono" style="min-width:36px;text-align:right">
              {{ \App\Models\Parametre::valeur('penalite_absence_pct','5') }}%
            </strong>
          </div>
          <div class="text-xs text-muted mt-1">Appliqué sur le salaire journalier</div>
        </div>

        <div class="form-group">
          <label class="form-label">% pénalité par retard</label>
          <div style="display:flex;align-items:center;gap:10px">
            <input type="range" name="penalite_retard_pct" min="0" max="20" step="1"
                   value="{{ \App\Models\Parametre::valeur('penalite_retard_pct','2') }}"
                   oninput="document.getElementById('ret-val').textContent=this.value+'%'"
                   style="flex:1;accent-color:var(--ink)">
            <strong id="ret-val" class="font-mono" style="min-width:36px;text-align:right">
              {{ \App\Models\Parametre::valeur('penalite_retard_pct','2') }}%
            </strong>
          </div>
        </div>

        <div style="padding:12px;background:var(--amber-bg);border-radius:var(--r);font-size:12px;color:var(--amber)">
          <strong>Exemple :</strong> Salaire 1 500 $/22j = 68,18 $/j. <br>
          Absence NJ → pénalité = 68,18 × {{ \App\Models\Parametre::valeur('penalite_absence_pct','5') }}% = {{ round(1500/22*\App\Models\Parametre::valeur('penalite_absence_pct',5)/100, 2) }} $
        </div>
      </div>
    </div>
  </div>

  <div class="grid grid-2 mb-4">
    {{-- Entreprise --}}
    <div class="card">
      <div class="card-header"><div class="card-title">🏢 Informations de l'entreprise</div></div>
      <div class="card-body">
        <div class="form-group">
          <label class="form-label">Nom de l'entreprise</label>
          <input type="text" name="nom_entreprise" class="form-control"
                 value="{{ \App\Models\Parametre::valeur('nom_entreprise','') }}">
        </div>
        <div class="form-group">
          <label class="form-label">Adresse</label>
          <input type="text" name="adresse_entreprise" class="form-control"
                 value="{{ \App\Models\Parametre::valeur('adresse_entreprise','') }}">
        </div>
        <div class="form-group">
          <label class="form-label">Téléphone</label>
          <input type="text" name="telephone_entreprise" class="form-control"
                 value="{{ \App\Models\Parametre::valeur('telephone_entreprise','') }}">
        </div>
        <div class="form-group">
          <label class="form-label">Logo (affiché sur les PDFs)</label>
          <input type="file" name="logo" class="form-control" accept="image/*">
        </div>
      </div>
    </div>

    {{-- Documents requis --}}
    <div class="card">
      <div class="card-header">
        <div class="card-title">📁 Types de documents requis</div>
        <button type="button" class="btn btn-sm btn-primary" onclick="ajouterDoc()">+ Ajouter</button>
      </div>
      <div class="card-body">
        <div id="docs-list" style="display:flex;flex-direction:column;gap:6px">
          @foreach(json_decode(\App\Models\Parametre::valeur('types_documents','[]'),true) as $i => $doc)
            <div style="display:flex;gap:6px;align-items:center" class="doc-item">
              <input type="text" name="types_documents[]" class="form-control" value="{{ $doc }}">
              <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">✕</button>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- Sécurité --}}
  <div class="card mb-4">
    <div class="card-header"><div class="card-title">🔐 Sécurité — Mot de passe administrateur</div></div>
    <div class="card-body">
      <div class="form-row form-row-3">
        <div class="form-group">
          <label class="form-label">Mot de passe actuel</label>
          <input type="password" name="current_password" class="form-control" placeholder="••••••••">
        </div>
        <div class="form-group">
          <label class="form-label">Nouveau mot de passe</label>
          <input type="password" name="new_password" class="form-control" placeholder="••••••••">
        </div>
        <div class="form-group">
          <label class="form-label">Confirmer le nouveau mot de passe</label>
          <input type="password" name="new_password_confirmation" class="form-control" placeholder="••••••••">
        </div>
      </div>
      <div class="alert alert-info mt-2">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="8" cy="8" r="6.5"/><path d="M8 7v4M8 5h.01"/></svg>
        Laissez vide pour conserver votre mot de passe actuel.
      </div>
    </div>
  </div>

  {{-- Biométrique --}}
  <div class="card">
    <div class="card-header"><div class="card-title">🖐 Appareil biométrique</div></div>
    <div class="card-body">
      <div class="form-row form-row-2">
        <div class="form-group">
          <label class="form-label">Adresse IP de l'appareil</label>
          <input type="text" name="biometric_ip" class="form-control font-mono"
                 value="{{ \App\Models\Parametre::valeur('biometric_ip','192.168.1.200') }}" placeholder="192.168.1.x">
        </div>
        <div class="form-group">
          <label class="form-label">Port TCP</label>
          <input type="number" name="biometric_port" class="form-control font-mono"
                 value="{{ \App\Models\Parametre::valeur('biometric_port','4370') }}">
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:10px">
        <button type="button" class="btn" onclick="testerConnexion()">🔌 Tester la connexion</button>
        <span id="connexion-status" class="text-sm text-muted"></span>
      </div>
    </div>
  </div>
</form>

@push('scripts')
<script>
function ajouterDoc() {
  const li = document.createElement('div');
  li.className = 'doc-item';
  li.style.cssText = 'display:flex;gap:6px;align-items:center';
  li.innerHTML = '<input type="text" name="types_documents[]" class="form-control" placeholder="Nom du document">'
               + '<button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">✕</button>';
  document.getElementById('docs-list').appendChild(li);
  li.querySelector('input').focus();
}
function testerConnexion() {
  const el = document.getElementById('connexion-status');
  el.textContent = 'Test en cours…';
  el.style.color = 'var(--ink3)';
  fetch('/admin/biometrique/statut')
    .then(r => r.json())
    .then(d => {
      el.textContent = d.connecte ? '✅ ' + d.message : '❌ ' + d.message;
      el.style.color = d.connecte ? 'var(--emerald)' : 'var(--crimson)';
    });
}
</script>
@endpush
@endsection
