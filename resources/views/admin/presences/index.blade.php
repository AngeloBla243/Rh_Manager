{{-- resources/views/admin/presences/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Présences')
@section('page-title', 'Gestion des présences')
@section('page-sub', 'Pointage du ' . $date->locale('fr')->isoFormat('dddd D MMMM YYYY'))

@section('topbar-actions')
  <input type="date" class="form-control" value="{{ $date->format('Y-m-d') }}"
         onchange="window.location='?date='+this.value" style="width:auto">
  <button class="btn btn-primary" onclick="document.getElementById('modal-manuel').style.display='flex'">
    + Pointage manuel
  </button>
@endsection

@section('content')

<div class="grid grid-2 mb-4">
  {{-- Stats --}}
  <div class="card">
    <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">
      <div style="text-align:center">
        <div style="font-size:32px;font-weight:700;color:var(--emerald);font-family:var(--mono)">{{ $stats['presents'] }}</div>
        <div class="text-sm text-muted">Présents</div>
        <div class="progress mt-2" style="height:4px">
          <div class="progress-fill progress-green" style="width:{{ $employes->count() > 0 ? $stats['presents']/$employes->count()*100 : 0 }}%"></div>
        </div>
      </div>
      <div style="text-align:center">
        <div style="font-size:32px;font-weight:700;color:var(--amber);font-family:var(--mono)">{{ $stats['retards'] }}</div>
        <div class="text-sm text-muted">Retards</div>
        <div class="progress mt-2" style="height:4px">
          <div class="progress-fill progress-amber" style="width:{{ $employes->count() > 0 ? $stats['retards']/$employes->count()*100 : 0 }}%"></div>
        </div>
      </div>
      <div style="text-align:center">
        <div style="font-size:32px;font-weight:700;color:var(--crimson);font-family:var(--mono)">{{ $stats['absents'] }}</div>
        <div class="text-sm text-muted">Absents</div>
        <div class="progress mt-2" style="height:4px">
          <div class="progress-fill progress-red" style="width:{{ $employes->count() > 0 ? $stats['absents']/$employes->count()*100 : 0 }}%"></div>
        </div>
      </div>
    </div>
  </div>

  {{-- Horaires configurés --}}
  <div class="card">
    <div class="card-header"><div class="card-title">Plage horaire paramétrée</div></div>
    <div class="card-body">
      <div style="display:flex;flex-direction:column;gap:8px">
        @php
          $heures = [
            ['Heure d\'arrivée normale', \App\Models\Parametre::valeur('heure_arrivee','08:00'), 'gray'],
            ['Limite sans retard',        \App\Models\Parametre::valeur('heure_limite_retard','08:30'), 'amber'],
            ['Heure de sortie',           \App\Models\Parametre::valeur('heure_sortie','17:00'), 'gray'],
          ];
        @endphp
        @foreach($heures as [$label, $val, $col])
          <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 12px;background:var(--{{ $col === 'amber' ? 'amber-bg' : 'bg2' }});border-radius:var(--r)">
            <span style="font-size:12.5px;color:var(--{{ $col === 'amber' ? 'amber' : 'ink2' }})">{{ $label }}</span>
            <strong class="font-mono" style="color:var(--{{ $col === 'amber' ? 'amber' : 'ink' }})">{{ $val }}</strong>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

{{-- Table présences --}}
<div class="card">
  <div class="card-header">
    <div class="card-title">Registre des présences</div>
    <a href="{{ route('admin.presences.rapport', ['mois' => now()->month, 'annee' => now()->year]) }}" class="btn btn-sm">
      📊 Rapport mensuel
    </a>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Employé</th>
          <th>Entrée</th>
          <th>Sortie</th>
          <th>Durée</th>
          <th>Mode</th>
          <th>Statut</th>
          <th>Validé</th>
        </tr>
      </thead>
      <tbody>
        @php
          $presencesParEmploye = $presences->keyBy('employe_id');
        @endphp
        @foreach($employes as $emp)
          @php $p = $presencesParEmploye->get($emp->id); @endphp
          <tr>
            <td>
              <div class="emp-cell">
                <div class="avatar">{{ mb_strtoupper(mb_substr($emp->nom,0,1).mb_substr($emp->prenom,0,1)) }}</div>
                <div>
                  <div class="emp-name">{{ $emp->nom }} {{ $emp->prenom }}</div>
                  <div class="emp-sub">{{ $emp->fonction }}</div>
                </div>
              </div>
            </td>
            <td class="font-mono">
              @if($p?->heure_entree)
                <span style="font-weight:500;color:var(--ink)">{{ \Carbon\Carbon::parse($p->heure_entree)->format('H:i') }}</span>
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td class="font-mono">
              @if($p?->heure_sortie)
                {{ \Carbon\Carbon::parse($p->heure_sortie)->format('H:i') }}
              @else
                <span class="text-muted">—</span>
              @endif
            </td>
            <td class="font-mono text-sm">
              @if($p?->heure_entree && $p?->heure_sortie)
                {{ \Carbon\Carbon::parse($p->heure_entree)->diff(\Carbon\Carbon::parse($p->heure_sortie))->format('%Hh%I') }}
              @else <span class="text-muted">—</span>
              @endif
            </td>
            <td>
              @if($p)
                <span class="badge badge-gray">{{ $p->mode_pointage === 'biometrique' ? '🖐 Bio' : '✏️ Manuel' }}</span>
              @else <span class="text-muted">—</span>
              @endif
            </td>
            <td>
              @if(!$p || !$p->heure_entree)
                <span class="badge badge-red">Absent</span>
              @elseif($p->est_retard)
                <span class="badge badge-amber">Retard +{{ $p->minutes_retard }}min</span>
              @else
                <span class="badge badge-green">Présent</span>
              @endif
            </td>
            <td style="text-align:center">
              @if($p?->est_valide)
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="var(--emerald)" stroke-width="2.5">
                  <circle cx="8" cy="8" r="6.5"/><path d="M5 8l2 2 4-4"/>
                </svg>
              @else
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="var(--ink4)" stroke-width="2">
                  <circle cx="8" cy="8" r="6.5"/><path d="M8 5v3M8 11h.01"/>
                </svg>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- Modal : pointage manuel --}}
<div class="modal-overlay" id="modal-manuel" style="display:none">
  <div class="modal modal-sm">
    <div class="modal-header">
      <span class="modal-title">Pointage manuel</span>
      <button class="modal-close" onclick="document.getElementById('modal-manuel').style.display='none'">✕</button>
    </div>
    <form method="POST" action="{{ route('admin.presences.manuel') }}">
      @csrf
      <div class="modal-body">
        <div class="alert alert-info mb-4">
          <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="8" cy="8" r="6.5"/><path d="M8 7v4M8 5h.01"/>
          </svg>
          Le pointage manuel est tracé et distinct du pointage biométrique.
        </div>
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
            <label class="form-label">Type <span class="required">*</span></label>
            <select name="type" class="form-control" required>
              <option value="entree">Entrée</option>
              <option value="sortie">Sortie</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Heure <span class="required">*</span></label>
            <input type="time" name="heure" class="form-control" value="{{ now()->format('H:i') }}" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Date</label>
          <input type="date" name="date" class="form-control" value="{{ $date->format('Y-m-d') }}">
        </div>
        <div class="form-group">
          <label class="form-label">Remarque</label>
          <input type="text" name="remarque" class="form-control" placeholder="Ex : retard justifié">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" onclick="document.getElementById('modal-manuel').style.display='none'">Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer le pointage</button>
      </div>
    </form>
  </div>
</div>

@endsection


{{-- ============================================================ --}}
{{-- resources/views/admin/absences/index.blade.php               --}}
{{-- ============================================================ --}}
{{-- NOTE: Create this as a separate file. Content below: --}}
