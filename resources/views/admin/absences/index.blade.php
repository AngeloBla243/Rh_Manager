{{-- resources/views/admin/absences/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Absences & Sanctions')
@section('page-title', 'Absences & Sanctions')
@section('page-sub', 'Suivi des absences et calcul des pénalités')

@section('topbar-actions')
  <select class="form-control" style="width:auto" onchange="window.location='?mois='+this.value.split('-')[1]+'&annee='+this.value.split('-')[0]">
    @for($i=0;$i<6;$i++)
      @php $d = \Carbon\Carbon::now()->subMonths($i); @endphp
      <option value="{{ $d->format('Y-m') }}" {{ $d->month==$mois&&$d->year==$annee?'selected':'' }}>
        {{ ucfirst($d->locale('fr')->isoFormat('MMMM YYYY')) }}
      </option>
    @endfor
  </select>
@endsection

@section('content')

<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px">
  <div class="stat-card">
    <div class="stat-label">Absences totales</div>
    <div class="stat-value">{{ $absences->count() }}</div>
    <div class="stat-sub">Ce mois</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Justifiées</div>
    <div class="stat-value" style="color:var(--amber)">{{ $absences->where('type','justifiee')->count() }}</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Non justifiées</div>
    <div class="stat-value" style="color:var(--crimson)">{{ $absences->where('type','non_justifiee')->count() }}</div>
    <div class="stat-sub stat-down">Total pénalités : {{ number_format($absences->sum('penalite'),2,',',' ') }} $</div>
  </div>
</div>

{{-- Config pénalités --}}
<div class="card mb-4">
  <div class="card-header">
    <div class="card-title">Configuration des pénalités</div>
    <span class="tag">Modifiable dans les paramètres</span>
  </div>
  <div class="card-body">
    <div class="grid grid-2">
      <div style="padding:12px;background:var(--crimson-bg);border-radius:var(--r);display:flex;justify-content:space-between;align-items:center">
        <div>
          <div style="font-size:12px;color:var(--crimson);font-weight:500">Absence non justifiée</div>
          <div class="text-sm text-muted mt-1">Par jour d'absence</div>
        </div>
        <div class="font-mono" style="font-size:22px;font-weight:700;color:var(--crimson)">
          {{ \App\Models\Parametre::valeur('penalite_absence_pct','5') }}%
        </div>
      </div>
      <div style="padding:12px;background:var(--amber-bg);border-radius:var(--r);display:flex;justify-content:space-between;align-items:center">
        <div>
          <div style="font-size:12px;color:var(--amber);font-weight:500">Retard</div>
          <div class="text-sm text-muted mt-1">Par occurrence de retard</div>
        </div>
        <div class="font-mono" style="font-size:22px;font-weight:700;color:var(--amber)">
          {{ \App\Models\Parametre::valeur('penalite_retard_pct','2') }}%
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Table absences --}}
<div class="card">
  <div class="card-header">
    <div class="card-title">Détail des absences</div>
    <button class="btn btn-sm btn-primary" onclick="document.getElementById('modal-abs').style.display='flex'">+ Enregistrer absence</button>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Employé</th><th>Date</th><th>Type</th><th>Motif</th><th>Pénalité</th><th>Statut</th><th>Actions</th></tr>
      </thead>
      <tbody>
        @forelse($absences as $ab)
          <tr>
            <td>
              <div class="emp-cell">
                <div class="avatar">{{ mb_strtoupper(mb_substr($ab->employe->nom,0,1).mb_substr($ab->employe->prenom,0,1)) }}</div>
                <span class="emp-name">{{ $ab->employe->nom }} {{ $ab->employe->prenom }}</span>
              </div>
            </td>
            <td class="font-mono text-sm">{{ $ab->date->format('d/m/Y') }}</td>
            <td>
              @if($ab->type==='justifiee')
                <span class="badge badge-amber">Justifiée</span>
              @elseif($ab->type==='non_justifiee')
                <span class="badge badge-red">Non justifiée</span>
              @elseif($ab->type==='conge')
                <span class="badge badge-blue">Congé</span>
              @else
                <span class="badge badge-gray">Férié</span>
              @endif
            </td>
            <td class="text-sm">{{ $ab->motif ?: '—' }}</td>
            <td class="font-mono text-sm" style="color:{{ $ab->penalite>0?'var(--crimson)':'var(--ink3)' }}">
              {{ $ab->penalite > 0 ? '- ' . number_format($ab->penalite,2,',',' ') . ' $' : '—' }}
            </td>
            <td>
              @if($ab->approuvee)
                <span class="badge badge-green">Approuvée</span>
              @else
                <span class="badge badge-amber">En attente</span>
              @endif
            </td>
            <td>
              <div class="table-actions">
                @if(!$ab->approuvee && $ab->type==='non_justifiee')
                  <button class="btn btn-sm btn-emerald" onclick="justifier({{ $ab->id }})">Justifier</button>
                @endif
                <form method="POST" action="{{ route('admin.absences.destroy',$ab) }}">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">✕</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="7">
            <div class="empty"><div class="empty-title">Aucune absence enregistrée</div></div>
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Modal justifier --}}
<div class="modal-overlay" id="modal-justif" style="display:none">
  <div class="modal modal-sm">
    <div class="modal-header">
      <span class="modal-title">Justifier une absence</span>
      <button class="modal-close" onclick="document.getElementById('modal-justif').style.display='none'">✕</button>
    </div>
    <form method="POST" id="form-justif" enctype="multipart/form-data">
      @csrf @method('PUT')
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Motif de justification <span class="required">*</span></label>
          <select name="motif" class="form-control" required>
            <option>Maladie</option><option>Congé autorisé</option><option>Mission officielle</option>
            <option>Urgence familiale</option><option>Cas de force majeure</option><option>Autre</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Document justificatif</label>
          <input type="file" name="document_justificatif" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
        </div>
        <div class="form-group">
          <label class="form-label">Notes complémentaires</label>
          <textarea name="notes" class="form-control" rows="3" placeholder="Détails…"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" onclick="document.getElementById('modal-justif').style.display='none'">Annuler</button>
        <button type="submit" class="btn btn-primary">Valider la justification</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
function justifier(id) {
  document.getElementById('form-justif').action = '/admin/absences/' + id + '/justifier';
  document.getElementById('modal-justif').style.display = 'flex';
}
</script>
@endpush
@endsection
