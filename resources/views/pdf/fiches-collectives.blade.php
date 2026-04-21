{{-- resources/views/pdf/fiches-collectives.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size:10px; color:#1a1a1a; }

.header { background:#18181a; color:white; padding:14px 20px; display:flex; justify-content:space-between; align-items:center; }
.header .title { font-size:14px; font-weight:bold; }
.header .sub { font-size:9px; opacity:.6; margin-top:2px; }
.header .period { font-size:12px; font-weight:bold; text-align:right; }

.totaux {
  display:flex; gap:0; border-bottom:1px solid #ddd;
  background:#f8f8f5;
}
.total-box { flex:1; padding:10px 14px; border-right:1px solid #eee; text-align:center; }
.total-box:last-child { border-right:none; }
.total-val { font-size:15px; font-weight:bold; font-family:monospace; }
.total-lbl { font-size:8.5px; color:#888; margin-top:2px; text-transform:uppercase; letter-spacing:.5px; }

table { width:100%; border-collapse:collapse; }
th { background:#2d2d2a; color:white; padding:7px 10px; text-align:left; font-size:9px; font-weight:bold; letter-spacing:.5px; }
td { padding:7px 10px; border-bottom:1px solid #f0f0ee; font-size:9.5px; vertical-align:middle; }
tr:nth-child(even) td { background:#fafaf8; }
.amount { text-align:right; font-family:monospace; }
.net { color:#1d6b45; font-weight:bold; }
.penalty { color:#b93535; }
.taux-cell { display:flex; align-items:center; gap:5px; }
.prog { height:4px; width:50px; background:#eee; border-radius:2px; overflow:hidden; flex-shrink:0; }
.prog-fill { height:100%; border-radius:2px; }

.footer { padding:10px 20px; text-align:right; font-size:8.5px; color:#bbb; border-top:1px solid #eee; margin-top:8px; }
</style>
</head>
<body>

<div class="header">
  <div>
    <div class="title">{{ \App\Models\Parametre::valeur('nom_entreprise','RH Manager') }}</div>
    <div class="sub">Fiches de paie collectives</div>
  </div>
  <div class="period">
    @php
      $moisLabels=['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    @endphp
    {{ $moisLabels[$mois] ?? '' }} {{ $annee }}
  </div>
</div>

<div class="totaux">
  <div class="total-box">
    <div class="total-val">{{ $salaires->count() }}</div>
    <div class="total-lbl">Employés</div>
  </div>
  <div class="total-box">
    <div class="total-val">{{ number_format($salaires->sum('salaire_brut'),0,',',' ') }} $</div>
    <div class="total-lbl">Total brut</div>
  </div>
  <div class="total-box">
    <div class="total-val" style="color:#b93535">- {{ number_format($salaires->sum('total_penalites'),0,',',' ') }} $</div>
    <div class="total-lbl">Pénalités</div>
  </div>
  <div class="total-box">
    <div class="total-val" style="color:#1d6b45">{{ number_format($salaires->sum('salaire_net'),0,',',' ') }} $</div>
    <div class="total-lbl">Total net à payer</div>
  </div>
  <div class="total-box">
    <div class="total-val">{{ $salaires->count() > 0 ? round($salaires->avg('taux_presence'),1) : 0 }}%</div>
    <div class="total-lbl">Taux présence moy.</div>
  </div>
</div>

<table>
  <thead>
    <tr>
      <th>#</th>
      <th>Employé</th>
      <th>Matricule</th>
      <th>Fonction</th>
      <th>Jrs trav.</th>
      <th>Absences</th>
      <th>Retards</th>
      <th>Pénalités</th>
      <th>Brut</th>
      <th>Net à payer</th>
      <th>Taux</th>
      <th>Statut</th>
    </tr>
  </thead>
  <tbody>
    @foreach($salaires as $i => $s)
      <tr>
        <td style="color:#aaa">{{ $i+1 }}</td>
        <td><strong>{{ $s->employe->nom }} {{ $s->employe->prenom }}</strong></td>
        <td style="font-family:monospace;font-size:9px;color:#888">{{ $s->employe->matricule }}</td>
        <td>{{ $s->employe->fonction }}</td>
        <td style="text-align:center">{{ $s->jours_travailles }}/{{ $s->jours_ouvres }}</td>
        <td style="text-align:center;color:{{ $s->nb_absences>0?'#b93535':'#888' }}">{{ $s->nb_absences }}</td>
        <td style="text-align:center;color:{{ $s->nb_retards>0?'#8a5c10':'#888' }}">{{ $s->nb_retards }}</td>
        <td class="amount penalty">{{ $s->total_penalites > 0 ? '- '.number_format($s->total_penalites,2) : '—' }}</td>
        <td class="amount">{{ number_format($s->salaire_brut,2) }}</td>
        <td class="amount net">{{ number_format($s->salaire_net,2) }}</td>
        <td>
          <div class="taux-cell">
            <div class="prog"><div class="prog-fill" style="width:{{ $s->taux_presence }}%;background:{{ $s->taux_presence>=85?'#1d6b45':($s->taux_presence>=70?'#d97706':'#b93535') }}"></div></div>
            {{ $s->taux_presence }}%
          </div>
        </td>
        <td>
          @if($s->statut_paiement==='paye')
            <span style="color:#1d6b45">✓ Payé</span>
          @else
            <span style="color:#8a5c10">En att.</span>
          @endif
        </td>
      </tr>
    @endforeach
    {{-- Ligne total --}}
    <tr style="background:#1a1a1a;color:white">
      <td colspan="8" style="font-weight:bold;padding:8px 10px">TOTAL GÉNÉRAL</td>
      <td class="amount" style="font-weight:bold">{{ number_format($salaires->sum('salaire_brut'),2) }}</td>
      <td class="amount" style="font-weight:bold;color:#6ee7b7">{{ number_format($salaires->sum('salaire_net'),2) }}</td>
      <td colspan="2"></td>
    </tr>
  </tbody>
</table>

<div class="footer">
  Généré le {{ now()->format('d/m/Y à H:i') }} — {{ \App\Models\Parametre::valeur('nom_entreprise','RH Manager') }} — Document confidentiel
</div>
</body>
</html>


{{-- ============================================================
     resources/views/pdf/carte-service.blade.php
     ============================================================ --}}
