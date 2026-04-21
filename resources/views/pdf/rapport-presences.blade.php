{{-- resources/views/pdf/rapport-presences.blade.php --}}
{{-- CORRECTION : ce fichier était mélangé avec carte-service.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size:10px; color:#1a1a1a; }

.header {
  background:#18181a; color:white;
  padding:14px 20px;
  display:flex; justify-content:space-between; align-items:center;
}
.header .title { font-size:14px; font-weight:bold; }
.header .sub   { font-size:9px; opacity:.6; margin-top:2px; }

.kpi-row { display:flex; gap:0; border-bottom:1px solid #e0e0d8; }
.kpi { flex:1; padding:10px 14px; text-align:center; border-right:1px solid #e8e8e0; }
.kpi:last-child { border-right:none; }
.kpi-val { font-size:18px; font-weight:bold; font-family:monospace; line-height:1; }
.kpi-lbl { font-size:8px; color:#999; margin-top:3px; text-transform:uppercase; letter-spacing:.5px; }

table { width:100%; border-collapse:collapse; }
thead th { background:#2d2d2a; color:white; padding:7px 10px; text-align:left; font-size:9px; font-weight:bold; }
tbody td { padding:7px 10px; border-bottom:1px solid #f2f2ee; }
tbody tr:nth-child(even) td { background:#fafaf8; }
tbody tr.summary td { background:#1a1a1a; color:white; font-weight:bold; }

.taux-cell { display:flex; align-items:center; gap:6px; }
.prog-outer { width:55px; height:5px; background:#eee; border-radius:3px; overflow:hidden; flex-shrink:0; }
.prog-inner { height:100%; border-radius:3px; }

.badge { display:inline-block; padding:2px 7px; border-radius:20px; font-size:8px; font-weight:bold; }
.badge-green { background:#edf6f1; color:#1d6b45; }
.badge-amber { background:#fdf4e3; color:#8a5c10; }
.badge-red   { background:#fbeaea; color:#b93535; }

.footer { padding:8px 16px; text-align:right; font-size:8px; color:#bbb; border-top:1px solid #eee; margin-top:8px; }
</style>
</head>
<body>

@php
  $moisLabels = ['','Janvier','Février','Mars','Avril','Mai','Juin',
                 'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
  $avgTaux  = collect($rapport)->avg('taux_presence') ?? 0;
  $totalAbs = collect($rapport)->sum('absences') ?? 0;
  $totalRet = collect($rapport)->sum('retards') ?? 0;
  $joursOuv = collect($rapport)->first()['jours_ouvres'] ?? 0;
  $entreprise = \App\Models\Parametre::valeur('nom_entreprise', 'RH Manager');
  $moisLabel  = $moisLabels[$mois] ?? '';
@endphp

<div class="header">
  <div>
    <div class="title">{{ $entreprise }}</div>
    <div class="sub">Rapport de présences — {{ $moisLabel }} {{ $annee }}</div>
  </div>
  <div style="text-align:right;font-size:9px">
    Généré le {{ now()->format('d/m/Y à H:i') }}<br>
    <span style="opacity:.6;font-size:8px">Document confidentiel</span>
  </div>
</div>

<div class="kpi-row">
  <div class="kpi"><div class="kpi-val">{{ count($rapport) }}</div><div class="kpi-lbl">Employés</div></div>
  <div class="kpi"><div class="kpi-val" style="color:#1d6b45">{{ round($avgTaux,1) }}%</div><div class="kpi-lbl">Taux moyen</div></div>
  <div class="kpi"><div class="kpi-val">{{ $joursOuv }}</div><div class="kpi-lbl">Jours ouvrés</div></div>
  <div class="kpi"><div class="kpi-val" style="color:#b93535">{{ $totalAbs }}</div><div class="kpi-lbl">Absences</div></div>
  <div class="kpi"><div class="kpi-val" style="color:#8a5c10">{{ $totalRet }}</div><div class="kpi-lbl">Retards</div></div>
</div>

<table>
  <thead>
    <tr>
      <th>#</th><th>Employé</th><th>Matricule</th><th>Fonction</th>
      <th style="text-align:center">Jrs ouvrés</th>
      <th style="text-align:center">Présents</th>
      <th style="text-align:center">Absences</th>
      <th style="text-align:center">Retards</th>
      <th>Taux</th><th>Appréciation</th>
    </tr>
  </thead>
  <tbody>
    @foreach(collect($rapport)->sortByDesc('taux_presence') as $i => $ligne)
      <tr>
        <td style="color:#aaa">{{ $i+1 }}</td>
        <td><strong>{{ $ligne['employe']->nom }} {{ $ligne['employe']->prenom }}</strong></td>
        <td style="font-family:monospace;font-size:9px;color:#888">{{ $ligne['employe']->matricule }}</td>
        <td>{{ $ligne['employe']->fonction }}</td>
        <td style="text-align:center;font-family:monospace">{{ $ligne['jours_ouvres'] }}</td>
        <td style="text-align:center;font-family:monospace;color:#1d6b45;font-weight:bold">{{ $ligne['jours_travailles'] }}</td>
        <td style="text-align:center;font-family:monospace;color:{{ $ligne['absences']>0?'#b93535':'#aaa' }}">{{ $ligne['absences'] }}</td>
        <td style="text-align:center;font-family:monospace;color:{{ $ligne['retards']>0?'#8a5c10':'#aaa' }}">{{ $ligne['retards'] }}</td>
        <td>
          <div class="taux-cell">
            <div class="prog-outer">
              <div class="prog-inner" style="width:{{ $ligne['taux_presence'] }}%;background:{{ $ligne['taux_presence']>=85?'#1d6b45':($ligne['taux_presence']>=70?'#d97706':'#b93535') }}"></div>
            </div>
            <span style="font-family:monospace;font-size:9.5px;font-weight:bold">{{ $ligne['taux_presence'] }}%</span>
          </div>
        </td>
        <td>
          @if($ligne['taux_presence']>=95) <span class="badge badge-green">Excellent</span>
          @elseif($ligne['taux_presence']>=85) <span class="badge badge-green">Bon</span>
          @elseif($ligne['taux_presence']>=70) <span class="badge badge-amber">Moyen</span>
          @else <span class="badge badge-red">Insuffisant</span>
          @endif
        </td>
      </tr>
    @endforeach
    <tr class="summary">
      <td colspan="4">SYNTHÈSE</td>
      <td style="text-align:center;font-family:monospace">{{ $joursOuv }}</td>
      <td style="text-align:center;font-family:monospace;color:#6ee7b7">{{ collect($rapport)->sum('jours_travailles') }}</td>
      <td style="text-align:center;font-family:monospace;color:#fca5a5">{{ $totalAbs }}</td>
      <td style="text-align:center;font-family:monospace;color:#fcd34d">{{ $totalRet }}</td>
      <td style="font-family:monospace;font-weight:bold;color:{{ $avgTaux>=85?'#6ee7b7':($avgTaux>=70?'#fcd34d':'#fca5a5') }}">{{ round($avgTaux,1) }}%</td>
      <td><span class="badge {{ $avgTaux>=85?'badge-green':'badge-amber' }}">{{ $avgTaux>=85?'Satisfaisant':'À améliorer' }}</span></td>
    </tr>
  </tbody>
</table>

<div style="display:flex;justify-content:flex-end;margin-top:24px;padding-right:16px">
  <div style="text-align:center">
    <div style="border-top:1px solid #333;width:180px;padding-top:6px;font-size:9px;color:#666">
      Visa Direction — {{ now()->format('d/m/Y') }}
    </div>
  </div>
</div>

<div class="footer">{{ $entreprise }} — Confidentiel — {{ $moisLabel }} {{ $annee }} — {{ now()->format('d/m/Y à H:i') }}</div>
</body>
</html>
