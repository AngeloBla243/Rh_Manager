{{-- resources/views/pdf/carte-service.blade.php --}}
{{-- Format carte bancaire : 85.6 x 54 mm = 242 x 153 points DomPDF --}}
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1a1a1a;
            width: 242pt;
            height: 153pt;
            overflow: hidden;
        }

        .card {
            width: 242pt;
            height: 153pt;
            background: #18181a;
            color: white;
            position: relative;
            overflow: hidden;
        }

        /* Motif de fond géométrique */
        .bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: .06;
            background-image:
                repeating-linear-gradient(45deg, #fff 0, #fff 1pt, transparent 0, transparent 50%),
                repeating-linear-gradient(-45deg, #fff 0, #fff 1pt, transparent 0, transparent 50%);
            background-size: 20pt 20pt;
        }

        .card-header {
            padding: 10pt 12pt 8pt;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1pt solid rgba(255, 255, 255, .1);
            position: relative;
            z-index: 1;
        }

        .company-name {
            font-size: 9pt;
            font-weight: bold;
            letter-spacing: -.2pt;
        }

        .company-sub {
            font-size: 7pt;
            opacity: .5;
            margin-top: 1pt;
        }

        .card-type {
            font-size: 7pt;
            font-weight: bold;
            letter-spacing: 1pt;
            text-transform: uppercase;
            opacity: .7;
            text-align: right;
        }

        .card-body {
            padding: 10pt 12pt;
            display: flex;
            gap: 10pt;
            align-items: flex-start;
            position: relative;
            z-index: 1;
        }

        .avatar {
            width: 42pt;
            height: 42pt;
            border-radius: 50%;
            background: rgba(255, 255, 255, .12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14pt;
            font-weight: bold;
            color: rgba(255, 255, 255, .7);
            border: 1pt solid rgba(255, 255, 255, .2);
            flex-shrink: 0;
        }

        .emp-info {
            flex: 1;
        }

        .emp-name {
            font-size: 12pt;
            font-weight: bold;
            letter-spacing: -.3pt;
            line-height: 1.1;
        }

        .emp-role {
            font-size: 8pt;
            opacity: .65;
            margin-top: 3pt;
        }

        .emp-meta {
            display: flex;
            gap: 12pt;
            margin-top: 8pt;
        }

        .meta-item .meta-lbl {
            font-size: 7pt;
            opacity: .5;
        }

        .meta-item .meta-val {
            font-size: 8pt;
            font-weight: bold;
            margin-top: 1pt;
        }

        .card-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 7pt 12pt;
            border-top: 1pt solid rgba(255, 255, 255, .08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(0, 0, 0, .2);
            z-index: 1;
        }

        .validity {}

        .validity-lbl {
            font-size: 7pt;
            opacity: .5;
        }

        .validity-val {
            font-size: 8pt;
            font-weight: bold;
            margin-top: 1pt;
        }

        .qr-placeholder {
            width: 28pt;
            height: 28pt;
            background: rgba(255, 255, 255, .1);
            border-radius: 3pt;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18pt;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="bg-pattern"></div>

        <div class="card-header">
            <div>
                <div class="company-name">{{ \App\Models\Parametre::valeur('nom_entreprise', 'RH Manager') }}</div>
                <div class="company-sub">{{ \App\Models\Parametre::valeur('adresse_entreprise', '') }}</div>
            </div>
            <div class="card-type">Carte de<br>Service</div>
        </div>

        <div class="card-body">
            <div class="avatar">
                {{ mb_strtoupper(mb_substr($employe->nom, 0, 1) . mb_substr($employe->prenom, 0, 1)) }}
            </div>
            <div class="emp-info">
                <div class="emp-name">{{ strtoupper($employe->nom) }}<br>{{ $employe->prenom }}</div>
                <div class="emp-role">{{ $employe->fonction }}</div>
                <div class="emp-meta">
                    <div class="meta-item">
                        <div class="meta-lbl">Matricule</div>
                        <div class="meta-val">{{ $employe->matricule }}</div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-lbl">Engagement</div>
                        <div class="meta-val">{{ $employe->annee_engagement }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="validity">
                <div class="validity-lbl">Valide du</div>
                <div class="validity-val">01/01/{{ $annee }} — 31/12/{{ $annee }}</div>
            </div>
            <div class="qr-placeholder">⬛</div>
        </div>
    </div>
</body>

</html>
