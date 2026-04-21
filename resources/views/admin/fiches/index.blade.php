{{-- resources/views/admin/fiches/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Fiches de paie')
@section('page-title', 'Fiches de paie')
@section('page-sub', 'Génération et impression des bulletins de salaire')

@section('content')

<div class="grid grid-2 mb-4">

  {{-- Génération individuelle --}}
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Fiche individuelle</div>
        <div class="section-sub">Un employé, un mois ou une année</div>
      </div>
      <svg width="20" height="20" viewBox="0 0 16 16" fill="none" stroke="var(--ink3)" stroke-width="1.5">
        <path d="M9.5 1.5H3a1 1 0 00-1 1v11a1 1 0 001 1h10a1 1 0 001-1V5.5z"/>
        <path d="M9 1.5V6h4.5M5 9h6M5 11.5h4"/>
      </svg>
    </div>
    <div class="card-body">
      <div class="form-group">
        <label class="form-label">Employé <span class="required">*</span></label>
        <select id="emp-select" class="form-control" onchange="updateSalaireSelect()">
          <option value="">— Sélectionner un employé —</option>
          @foreach($employes as $e)
            <option value="{{ $e->id }}">{{ $e->nom }} {{ $e->prenom }} ({{ $e->matricule }})</option>
          @endforeach
        </select>
      </div>

      <div class="form-row form-row-2">
        <div class="form-group">
          <label class="form-label">Période</label>
          <select id="periode-select" class="form-control">
            @for($i=0; $i<12; $i++)
              @php $d = \Carbon\Carbon::now()->subMonths($i); @endphp
              <option value="{{ $d->month }}-{{ $d->year }}">
                {{ ucfirst($d->locale('fr')->isoFormat('MMMM YYYY')) }}
              </option>
            @endfor
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Type de fiche</label>
          <select id="type-fiche" class="form-control">
            <option value="mensuelle">Mensuelle</option>
            <option value="annuelle">Récapitulatif annuel</option>
          </select>
        </div>
      </div>

      <div id="salaire-info" style="display:none;margin-bottom:14px">
        <div style="padding:12px;background:var(--bg2);border-radius:var(--r)">
          <div style="display:flex;justify-content:space-between;font-size:12.5px">
            <span class="text-muted">Salaire net calculé</span>
            <strong id="salaire-net-val" class="font-mono" style="color:var(--emerald)">—</strong>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:12px;margin-top:4px">
            <span class="text-muted">Taux de présence</span>
            <span id="salaire-taux-val" class="font-mono">—</span>
          </div>
        </div>
      </div>

      <button class="btn btn-primary w-full" style="justify-content:center;padding:10px"
              onclick="genererFicheIndividuelle()">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M8 2v8M4 7l4 4 4-4M2 13h12"/>
        </svg>
        Générer et télécharger le PDF
      </button>
    </div>
  </div>

  {{-- Génération collective --}}
  <div class="card">
    <div class="card-header">
      <div>
        <div class="card-title">Fiches collectives</div>
        <div class="section-sub">Tous les employés en un seul document</div>
      </div>
      <svg width="20" height="20" viewBox="0 0 16 16" fill="none" stroke="var(--ink3)" stroke-width="1.5">
        <path d="M4 4h8M4 7h8M4 10h5"/>
        <rect x="1" y="1" width="14" height="14" rx="1.5"/>
      </svg>
    </div>
    <div class="card-body">
      <div class="form-row form-row-2">
        <div class="form-group">
          <label class="form-label">Mois <span class="required">*</span></label>
          <select name="mois" id="coll-mois" class="form-control">
            @for($i=0;$i<12;$i++)
              @php $d=\Carbon\Carbon::now()->subMonths($i); @endphp
              <option value="{{ $d->month }}" data-annee="{{ $d->year }}">
                {{ ucfirst($d->locale('fr')->isoFormat('MMMM YYYY')) }}
              </option>
            @endfor
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Filtrer par fonction</label>
          <select name="fonction" class="form-control">
            <option value="">Toutes les fonctions</option>
            @foreach($fonctions as $f)
              <option value="{{ $f }}">{{ $f }}</option>
            @endforeach
          </select>
        </div>
      </div>

      @php
        $dernierMois = \App\Models\Salaire::where('mois', now()->month)->where('annee', now()->year)->count();
      @endphp

      <div style="padding:12px;background:var(--bg2);border-radius:var(--r);margin-bottom:14px">
        <div style="display:flex;justify-content:space-between;font-size:12.5px">
          <span class="text-muted">Fiches disponibles ce mois</span>
          <strong class="font-mono">{{ $dernierMois }}</strong>
        </div>
        @if($dernierMois === 0)
          <div class="alert alert-warning mt-2" style="margin-bottom:0;font-size:12px">
            Aucun salaire calculé pour ce mois. Rendez-vous dans <a href="{{ route('admin.salaires.index') }}" style="font-weight:600">Salaires</a>.
          </div>
        @endif
      </div>

      <a href="{{ route('admin.pdf.fiches-collectives', ['mois'=>now()->month,'annee'=>now()->year]) }}"
         class="btn btn-primary w-full" style="justify-content:center;padding:10px" target="_blank">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M8 2v8M4 7l4 4 4-4M2 13h12"/>
        </svg>
        Générer toutes les fiches PDF
      </a>

      <div style="display:flex;gap:8px;margin-top:8px">
        <a href="{{ route('admin.pdf.rapport-presences', ['mois'=>now()->month,'annee'=>now()->year]) }}"
           class="btn w-full" style="justify-content:center;font-size:12.5px" target="_blank">
          📊 Rapport de présences
        </a>
      </div>
    </div>
  </div>
</div>

{{-- Historique des fiches générées --}}
<div class="card">
  <div class="card-header">
    <div class="card-title">Historique — Dernières fiches disponibles</div>
    <div class="text-sm text-muted">Classées par mois décroissant</div>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Employé</th>
          <th>Période</th>
          <th>Brut</th>
          <th>Pénalités</th>
          <th>Net</th>
          <th>Taux présence</th>
          <th>Statut paiement</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($salaires as $s)
          <tr>
            <td>
              <div class="emp-cell">
                <div class="avatar">
                  {{ mb_strtoupper(mb_substr($s->employe->nom,0,1).mb_substr($s->employe->prenom,0,1)) }}
                </div>
                <div>
                  <div class="emp-name">{{ $s->employe->nom }} {{ $s->employe->prenom }}</div>
                  <div class="emp-sub">{{ $s->employe->fonction }}</div>
                </div>
              </div>
            </td>
            <td class="font-mono text-sm">{{ $s->mois_libelle }} {{ $s->annee }}</td>
            <td class="font-mono">{{ number_format($s->salaire_brut, 2, ',', ' ') }} $</td>
            <td class="font-mono text-sm" style="color:{{ $s->total_penalites>0?'var(--crimson)':'var(--ink3)' }}">
              {{ $s->total_penalites > 0 ? '- '.number_format($s->total_penalites,2,',',' ').' $' : '—' }}
            </td>
            <td class="font-mono" style="font-weight:600;color:var(--emerald)">
              {{ number_format($s->salaire_net, 2, ',', ' ') }} $
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <div class="progress" style="width:55px">
                  <div class="progress-fill
                    {{ $s->taux_presence>=85?'progress-green':($s->taux_presence>=70?'progress-amber':'progress-red') }}"
                    style="width:{{ $s->taux_presence }}%"></div>
                </div>
                <span class="font-mono text-xs">{{ $s->taux_presence }}%</span>
              </div>
            </td>
            <td>
              @if($s->statut_paiement === 'paye')
                <span class="badge badge-green">Payé {{ $s->date_paiement?->format('d/m') }}</span>
              @elseif($s->statut_paiement === 'annule')
                <span class="badge badge-red">Annulé</span>
              @else
                <span class="badge badge-amber">En attente</span>
              @endif
            </td>
            <td>
              <a href="{{ route('admin.pdf.fiche-individuelle', $s) }}"
                 class="btn btn-sm" target="_blank">
                📄 PDF
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8">
              <div class="empty">
                <div class="empty-icon">📄</div>
                <div class="empty-title">Aucune fiche de paie générée</div>
                <div class="empty-sub">Calculez d'abord les salaires depuis le module Salaires.</div>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($salaires->hasPages())
    <div class="card-footer" style="display:flex;justify-content:flex-end">
      {{ $salaires->links() }}
    </div>
  @endif
</div>

@push('scripts')
<script>
const salaireData = @json($salaireData ?? []);

function updateSalaireSelect() {
  const empId = document.getElementById('emp-select').value;
  const info  = document.getElementById('salaire-info');
  if (!empId || !salaireData[empId]) { info.style.display = 'none'; return; }
  const d = salaireData[empId];
  document.getElementById('salaire-net-val').textContent  = d.net  + ' $';
  document.getElementById('salaire-taux-val').textContent = d.taux + '%';
  info.style.display = '';
}

function genererFicheIndividuelle() {
  const empId   = document.getElementById('emp-select').value;
  const periode = document.getElementById('periode-select').value;
  if (!empId)   { alert('Veuillez sélectionner un employé.'); return; }
  if (!periode) { alert('Veuillez sélectionner une période.'); return; }
  const [mois, annee] = periode.split('-');
  window.open(`/admin/pdf/fiche-individuelle/${empId}?mois=${mois}&annee=${annee}`, '_blank');
}
</script>
@endpush
@endsection
