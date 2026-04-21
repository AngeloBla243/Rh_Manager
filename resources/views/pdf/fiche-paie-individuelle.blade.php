{{-- resources/views/pdf/fiche-paie-individuelle.blade.php --}}
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size:11px; color:#1a1a1a; }

.header {
  background:#18181a; color:white;
  padding:18px 24px;
  display:flex; justify-content:space-between; align-items:center;
}
.header-left .company { font-size:16px; font-weight:bold; letter-spacing:-.3px; }
.header-left .company-sub { font-size:10px; opacity:.6; margin-top:2px; }
.header-right { text-align:right; }
.header-right .doc-type { font-size:14px; font-weight:bold; letter-spacing:1px; text-transform:uppercase; }
.header-right .doc-period { font-size:10px; opacity:.6; margin-top:2px; }

.body { padding:20px 24px; }

.emp-block {
  display:flex; align-items:flex-start; gap:16px;
  padding:14px 16px; background:#f5f5f2; border-radius:8px;
  margin-bottom:18px;
}
.emp-photo {
  width:48px; height:48px; border-radius:50%;
  background:#ddd; display:flex; align-items:center; justify-content:center;
  font-size:16px; font-weight:bold; color:#555; flex-shrink:0;
  border:2px solid #ccc;
}
.emp-name { font-size:15px; font-weight:bold; }
.emp-role { font-size:11px; color:#666; margin-top:2px; }
.emp-meta { display:flex; gap:16px; margin-top:8px; }
.emp-meta-item { font-size:10px; }
.emp-meta-label { color:#999; }
.emp-meta-val { font-weight:bold; color:#333; }

.badge-actif { display:inline-block; padding:2px 8px; background:#edf6f1; color:#1d6b45; border-radius:20px; font-size:9px; font-weight:bold; margin-top:6px; }

.section-title {
  font-size:9px; text-transform:uppercase; letter-spacing:1px;
  color:#999; border-bottom:1px solid #eee; padding-bottom:4px;
  margin-bottom:10px; margin-top:16px;
}

table { width:100%; border-collapse:collapse; }
th {
  background:#f0f0ee; padding:7px 10px;
  text-align:left; font-size:9.5px; color:#666; font-weight:bold;
  text-transform:uppercase; letter-spacing:.5px;
}
td { padding:8px 10px; border-bottom:1px solid #f0f0ee; font-size:10.5px; }
tr.subtotal td { background:#f8f8f5; font-weight:bold; }
tr.total td { background:#1d6b45; color:white; font-weight:bold; font-size:12px; }
tr.deduct td { color:#b93535; }
.amount { text-align:right; font-family:monospace; }

.presence-grid { display:flex; gap:10px; flex-wrap:wrap; margin-top:10px; }
.pres-box {
  flex:1; min-width:100px; padding:10px 12px;
  border:1px solid #eee; border-radius:6px; text-align:center;
}
.pres-val { font-size:22px; font-weight:bold; font-family:monospace; color:#1a1a1a; }
.pres-label { font-size:9px; color:#999; margin-top:3px; }

.progress-bar { height:6px; background:#eee; border-radius:3px; margin-top:4px; overflow:hidden; }
.progress-fill { height:100%; border-radius:3px; }

.signatures { display:flex; justify-content:space-between; margin-top:30px; }
.sig-block { text-align:center; }
.sig-line { border-top:1px solid #333; width:160px; padding-top:6px; font-size:10px; color:#666; }

.footer {
  margin-top:24px; border-top:1px solid #eee; padding-top:10px;
  font-size:9px; color:#bbb; text-align:center;
}
</style>
</head>
<body>

<div class="header">
  <div class="header-left">
    <div class="company">{{ \App\Models\Parametre::valeur('nom_entreprise', 'RH Manager') }}</div>
    <div class="company-sub">{{ \App\Models\Parametre::valeur('adresse_entreprise', '') }}</div>
  </div>
  <div class="header-right">
    <div class="doc-type">Fiche de paie</div>
    <div class="doc-period">{{ $salaire->mois_libelle }} {{ $salaire->annee }}</div>
  </div>
</div>

<div class="body">

  {{-- Bloc employé --}}
  <div class="emp-block">
    <div class="emp-photo">{{ mb_strtoupper(mb_substr($employe->nom,0,1).mb_substr($employe->prenom,0,1)) }}</div>
    <div>
      <div class="emp-name">{{ strtoupper($employe->nom) }} {{ $employe->prenom }}</div>
      <div class="emp-role">{{ $employe->fonction }}</div>
      <div class="emp-meta">
        <div class="emp-meta-item"><div class="emp-meta-label">Matricule</div><div class="emp-meta-val">{{ $employe->matricule }}</div></div>
        <div class="emp-meta-item"><div class="emp-meta-label">Engagement</div><div class="emp-meta-val">{{ $employe->annee_engagement }}</div></div>
        <div class="emp-meta-item"><div class="emp-meta-label">Ancienneté</div><div class="emp-meta-val">{{ $employe->anciennete }} an(s)</div></div>
      </div>
      <div class="badge-actif">{{ strtoupper($employe->statut) }}</div>
    </div>
  </div>

  {{-- Tableau salaire --}}
  <div class="section-title">Détail de la rémunération</div>
  <table>
    <thead>
      <tr><th>Libellé</th><th>Base</th><th class="amount">Montant ($)</th></tr>
    </thead>
    <tbody>
      <tr>
        <td>Salaire de base mensuel</td>
        <td>{{ $salaire->jours_travailles }}/{{ $salaire->jours_ouvres }} jours</td>
        <td class="amount">{{ number_format($salaire->salaire_brut, 2) }}</td>
      </tr>
      @if($salaire->penalites_absences > 0)
      <tr class="deduct">
        <td>Pénalités absences non justifiées ({{ $salaire->nb_absences }} jrs)</td>
        <td>{{ \App\Models\Parametre::valeur('penalite_absence_pct', 5) }}% / jour</td>
        <td class="amount">- {{ number_format($salaire->penalites_absences, 2) }}</td>
      </tr>
      @endif
      @if($salaire->penalites_retards > 0)
      <tr class="deduct">
        <td>Pénalités retards ({{ $salaire->nb_retards }} fois)</td>
        <td>{{ \App\Models\Parametre::valeur('penalite_retard_pct', 2) }}% / retard</td>
        <td class="amount">- {{ number_format($salaire->penalites_retards, 2) }}</td>
      </tr>
      @endif
      <tr class="total">
        <td colspan="2">NET À PAYER</td>
        <td class="amount">{{ number_format($salaire->salaire_net, 2) }}</td>
      </tr>
    </tbody>
  </table>

  {{-- Présences --}}
  <div class="section-title">Récapitulatif des présences</div>
  <div class="presence-grid">
    <div class="pres-box">
      <div class="pres-val">{{ $salaire->jours_ouvres }}</div>
      <div class="pres-label">Jours ouvrés</div>
    </div>
    <div class="pres-box">
      <div class="pres-val" style="color:#1d6b45">{{ $salaire->jours_travailles }}</div>
      <div class="pres-label">Jours travaillés</div>
    </div>
    <div class="pres-box">
      <div class="pres-val" style="color:#b93535">{{ $salaire->nb_absences }}</div>
      <div class="pres-label">Absences</div>
    </div>
    <div class="pres-box">
      <div class="pres-val" style="color:#8a5c10">{{ $salaire->nb_retards }}</div>
      <div class="pres-label">Retards</div>
    </div>
    <div class="pres-box" style="min-width:140px">
      <div class="pres-val" style="font-size:18px">{{ $salaire->taux_presence }}%</div>
      <div class="pres-label">Taux de présence</div>
      <div class="progress-bar"><div class="progress-fill" style="width:{{ $salaire->taux_presence }}%;background:{{ $salaire->taux_presence>=85?'#1d6b45':($salaire->taux_presence>=70?'#d97706':'#b93535') }}"></div></div>
    </div>
  </div>

  {{-- Signatures --}}
  <div class="signatures">
    <div class="sig-block"><div class="sig-line">Signature de l'employé</div></div>
    <div class="sig-block"><div class="sig-line">Cachet et signature de la Direction</div></div>
  </div>

</div>

<div class="footer">
  Document généré automatiquement par RH Manager le {{ now()->format('d/m/Y à H:i') }} — Confidentiel
</div>
</body>
</html>


{{-- ============================================================
     resources/views/pdf/fiches-collectives.blade.php
     ============================================================ --}}
