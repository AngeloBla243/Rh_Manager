{{-- resources/views/admin/notifications/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Notifications & Alertes')
@section('page-title', 'Notifications & Alertes')
@section('page-sub', $nonLues . ' alerte(s) non lue(s)')

@section('topbar-actions')
  @if($nonLues > 0)
    <form method="POST" action="{{ route('admin.notifications.tout-lire') }}">
      @csrf
      <button type="submit" class="btn">✓ Tout marquer comme lu</button>
    </form>
  @endif
  <form method="POST" action="{{ route('admin.notifications.generer') }}">
    @csrf
    <button type="submit" class="btn btn-primary">↻ Vérifier maintenant</button>
  </form>
@endsection

@section('content')

{{-- Résumé par type --}}
<div class="stats-grid mb-4">
  @php
    $types = [
      ['absence',           '🚨', 'Absences',       'crimson'],
      ['retards_frequents', '⏰', 'Retards fréq.',  'amber'],
      ['paiement_salaire',  '💰', 'Paiements',      'emerald'],
      ['fin_contrat',       '📋', 'Fins de contrat','cobalt'],
    ];
  @endphp
  @foreach($types as [$type, $icon, $label, $color])
    <div class="stat-card" style="cursor:pointer" onclick="filtrer('{{ $type }}')">
      <div style="font-size:22px;margin-bottom:8px">{{ $icon }}</div>
      <div class="stat-label">{{ $label }}</div>
      <div class="stat-value" style="color:var(--{{ $color }})">
        {{ $alertes->where('type', $type)->count() }}
      </div>
      <div class="stat-sub">
        {{ $alertes->where('type', $type)->where('lue', false)->count() }} non lu(e)(s)
      </div>
    </div>
  @endforeach
</div>

{{-- Filtres --}}
<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
  <button class="btn btn-sm {{ !request('type') && !request('priorite') ? 'btn-primary' : '' }}"
          onclick="window.location='?'">Toutes</button>
  <button class="btn btn-sm {{ request('lue')==='0' ? 'btn-primary' : '' }}"
          onclick="window.location='?lue=0'">Non lues</button>
  @foreach(['critique'=>'Critiques','haute'=>'Haute priorité'] as $val => $label)
    <button class="btn btn-sm {{ request('priorite')===$val ? 'btn-primary' : '' }}"
            onclick="window.location='?priorite={{ $val }}'">{{ $label }}</button>
  @endforeach
</div>

{{-- Liste alertes --}}
<div class="card">
  @forelse($alertes as $alerte)
    <div style="display:flex;gap:14px;padding:14px 20px;border-bottom:1px solid var(--border);
                background:{{ !$alerte->lue ? 'var(--cobalt-bg)' : 'transparent' }};transition:background .15s"
         id="alerte-{{ $alerte->id }}">

      {{-- Icône priorité --}}
      <div style="font-size:20px;flex-shrink:0;margin-top:2px">{{ $alerte->priorite_icone }}</div>

      <div style="flex:1;min-width:0">
        <div style="display:flex;align-items:baseline;gap:10px;flex-wrap:wrap;margin-bottom:4px">
          <span style="font-size:13.5px;font-weight:{{ !$alerte->lue ? '600' : '500' }};color:var(--ink)">
            {{ $alerte->titre }}
          </span>
          <span class="badge badge-{{ $alerte->priorite_classe }}" style="font-size:10px">
            {{ ucfirst($alerte->priorite) }}
          </span>
          @if(!$alerte->lue)
            <span class="badge badge-blue" style="font-size:10px">Nouvelle</span>
          @endif
        </div>
        <div style="font-size:13px;color:var(--ink2);margin-bottom:6px">{{ $alerte->message }}</div>
        <div style="display:flex;align-items:center;gap:14px">
          <span class="text-xs text-muted">
            {{ $alerte->created_at->locale('fr')->diffForHumans() }}
          </span>
          @if($alerte->employe)
            <a href="{{ route('admin.employes.show', $alerte->employe) }}"
               class="text-xs" style="color:var(--cobalt)">
              → Voir la fiche de {{ $alerte->employe->prenom }}
            </a>
          @endif
          @if($alerte->type === 'fin_contrat' && isset($alerte->meta['contrat_id']))
            <a href="{{ route('admin.contrats.show', $alerte->meta['contrat_id']) }}"
               class="text-xs" style="color:var(--cobalt)">→ Voir le contrat</a>
          @endif
        </div>
      </div>

      <div style="flex-shrink:0;display:flex;flex-direction:column;gap:6px;align-items:flex-end">
        @if(!$alerte->lue)
          <form method="POST" action="{{ route('admin.notifications.lire', $alerte) }}">
            @csrf
            <button type="submit" class="btn btn-xs">✓ Lu</button>
          </form>
        @else
          <span class="text-xs text-muted">Lu {{ $alerte->lue_at?->format('d/m H:i') }}</span>
        @endif
        <form method="POST" action="{{ route('admin.notifications.destroy', $alerte) }}">
          @csrf @method('DELETE')
          <button type="submit" class="btn btn-xs btn-danger">✕</button>
        </form>
      </div>
    </div>
  @empty
    <div class="empty" style="padding:48px">
      <div class="empty-icon">🔔</div>
      <div class="empty-title">Aucune notification</div>
      <div class="empty-sub">Cliquez sur « Vérifier maintenant » pour analyser les alertes.</div>
    </div>
  @endforelse
</div>

@if($alertes->hasPages())
  <div style="margin-top:12px;display:flex;justify-content:flex-end">{{ $alertes->links() }}</div>
@endif

@push('scripts')
<script>
function filtrer(type) {
  window.location = '?type=' + type;
}
</script>
@endpush
@endsection


{{-- ============================================================
     resources/views/layouts/admin.blade.php  — SIDEBAR MISE À JOUR
     Ajouter ces nav-links dans la sidebar (section Outils)
     ============================================================ --}}
{{--
// Contrats (dans section RH)
<a href="{{ route('admin.contrats.index') }}"
   class="nav-link {{ request()->routeIs('admin.contrats*') ? 'active' : '' }}">
  <svg class="icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
    <path d="M9.5 1.5H3a1 1 0 00-1 1v11a1 1 0 001 1h10a1 1 0 001-1V5.5z"/>
    <path d="M9 1.5V6h4.5M5 9h6M5 11.5h4"/>
  </svg>
  Contrats
</a>

// Rapports (dans section Finance)
<a href="{{ route('admin.rapports.index') }}"
   class="nav-link {{ request()->routeIs('admin.rapports*') ? 'active' : '' }}">
  <svg class="icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
    <path d="M1.5 13L5 8.5l3 2.5 3.5-5 3 3"/><path d="M1 15h14"/>
  </svg>
  Rapports
</a>

// Notifications avec badge (dans section Outils)
<a href="{{ route('admin.notifications.index') }}"
   class="nav-link {{ request()->routeIs('admin.notifications*') ? 'active' : '' }}"
   style="position:relative">
  <svg class="icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6">
    <path d="M8 1.5A4.5 4.5 0 013.5 6v3.5L2 11h12l-1.5-1.5V6A4.5 4.5 0 018 1.5z"/>
    <path d="M6.5 12.5a1.5 1.5 0 003 0"/>
  </svg>
  Notifications
  @php $nbAlertes = \App\Models\AlerteRh::nonLues()->count(); @endphp
  @if($nbAlertes > 0)
    <span style="margin-left:auto;background:var(--crimson);color:white;border-radius:20px;font-size:10px;font-weight:700;padding:1px 6px;min-width:18px;text-align:center">
      {{ $nbAlertes > 99 ? '99+' : $nbAlertes }}
    </span>
  @endif
</a>
--}}
