{{-- resources/views/pointage/index.blade.php — VERSION AMÉLIORÉE --}}
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pointage — {{ \App\Models\Parametre::valeur('nom_entreprise', 'RH Manager') }}</title>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg: #f5f4f0;
            --surface: #fff;
            --ink: #18181a;
            --ink2: #52524e;
            --ink3: #96958f;
            --ink4: #c4c3bc;
            --border: #e2e1dc;
            --emerald: #1d6b45;
            --emerald-bg: #edf6f1;
            --crimson: #b93535;
            --crimson-bg: #fbeaea;
            --amber: #8a5c10;
            --amber-bg: #fdf4e3;
            --cobalt: #1a4fa0;
            --cobalt-bg: #e8eef9;
            --r: 8px;
            --rl: 14px;
            --rxl: 20px;
            --font: 'DM Sans', system-ui, sans-serif;
            --mono: 'DM Mono', monospace;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        body {
            font-family: var(--font);
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased
        }

        /* Header barre du haut */
        .top-bar {
            width: 100%;
            max-width: 900px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .company-name {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -.3px;
            color: var(--ink)
        }

        .company-sub {
            font-size: 11px;
            color: var(--ink3);
            margin-top: 1px
        }

        .device-status {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 12px;
            color: var(--ink3)
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0
        }

        .dot-green {
            background: var(--emerald);
            box-shadow: 0 0 6px var(--emerald);
            animation: pulse 2s infinite
        }

        .dot-amber {
            background: #d97706
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .4
            }
        }

        /* Layout principal */
        .layout {
            width: 100%;
            max-width: 900px;
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 20px;
            align-items: start
        }

        @media(max-width:700px) {
            .layout {
                grid-template-columns: 1fr
            }
        }

        /* Card gauche — pointage */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--rxl);
            padding: 28px 24px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .05)
        }

        /* Horloge */
        .clock {
            font-family: var(--mono);
            font-size: 52px;
            font-weight: 400;
            letter-spacing: -3px;
            color: var(--ink);
            line-height: 1;
            text-align: center
        }

        .clock-date {
            text-align: center;
            font-size: 13px;
            color: var(--ink3);
            margin-top: 6px;
            margin-bottom: 24px
        }

        /* Horaires */
        .horaires {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            border: 1px solid var(--border);
            border-radius: var(--r);
            overflow: hidden;
            margin-bottom: 20px
        }

        .horaire-slot {
            padding: 8px 10px;
            text-align: center;
            border-right: 1px solid var(--border)
        }

        .horaire-slot:last-child {
            border-right: none
        }

        .horaire-time {
            font-family: var(--mono);
            font-size: 15px;
            font-weight: 600
        }

        .horaire-lbl {
            font-size: 9px;
            color: var(--ink3);
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: .5px
        }

        .slot-retard {
            background: var(--amber-bg)
        }

        .slot-retard .horaire-time {
            color: var(--amber)
        }

        /* Empreinte */
        .fp-wrap {
            text-align: center;
            margin-bottom: 20px
        }

        .fp-ring {
            width: 110px;
            height: 140px;
            border: 2px solid var(--border);
            border-radius: 18px;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            background: var(--bg);
            transition: all .2s;
        }

        .fp-ring:hover {
            border-color: var(--ink);
            transform: scale(1.02)
        }

        .fp-ring.scanning {
            border-color: var(--cobalt);
            animation: borderPulse .8s infinite
        }

        .fp-ring.success {
            border-color: var(--emerald);
            background: var(--emerald-bg)
        }

        .fp-ring.error {
            border-color: var(--crimson);
            background: var(--crimson-bg)
        }

        @keyframes borderPulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(26, 79, 160, .3)
            }

            50% {
                box-shadow: 0 0 0 6px rgba(26, 79, 160, 0)
            }
        }

        .fp-scan {
            position: absolute;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--cobalt), transparent);
            animation: scan 1.8s ease-in-out infinite
        }

        @keyframes scan {
            0% {
                top: 0;
                opacity: 0
            }

            20% {
                opacity: 1
            }

            80% {
                opacity: 1
            }

            100% {
                top: 100%;
                opacity: 0
            }
        }

        .fp-status {
            font-size: 12px;
            color: var(--ink3)
        }

        .fp-status.success {
            color: var(--emerald);
            font-weight: 500
        }

        .fp-status.error {
            color: var(--crimson)
        }

        /* Boutons pointage */
        .btn-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px
        }

        .btn-point {
            padding: 16px 12px;
            border-radius: var(--rl);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            font-family: var(--font);
            transition: all .18s;
        }

        .btn-entree {
            background: var(--ink);
            color: white;
            border-color: var(--ink)
        }

        .btn-entree:hover {
            background: #323230;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, .2)
        }

        .btn-sortie {
            background: var(--surface);
            color: var(--ink);
            border-color: var(--border)
        }

        .btn-sortie:hover {
            background: var(--bg);
            transform: translateY(-1px)
        }

        .btn-icon {
            font-size: 22px;
            line-height: 1
        }

        .btn-label {
            font-size: 11px;
            opacity: .7;
            font-weight: 400
        }

        /* Message résultat */
        .msg {
            padding: 14px 16px;
            border-radius: var(--rl);
            display: none;
            animation: fadeUp .25s ease
        }

        @keyframes fadeUp {
            from {
                transform: translateY(8px);
                opacity: 0
            }

            to {
                transform: translateY(0);
                opacity: 1
            }
        }

        .msg.success {
            background: var(--emerald-bg);
            border: 1px solid rgba(29, 107, 69, .15)
        }

        .msg.warning {
            background: var(--amber-bg);
            border: 1px solid rgba(138, 92, 16, .15)
        }

        .msg.error {
            background: var(--crimson-bg);
            border: 1px solid rgba(185, 53, 53, .15)
        }

        .msg-name {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 2px
        }

        .msg-detail {
            font-size: 12px;
            opacity: .8
        }

        /* Card droite — présences du jour */
        .card-right {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--rxl);
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .05)
        }

        .card-right-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center
        }

        .card-right-title {
            font-size: 14px;
            font-weight: 600
        }

        /* Stats mini */
        .mini-stats {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 0;
            border-bottom: 1px solid var(--border)
        }

        .mini-stat {
            padding: 12px 16px;
            text-align: center;
            border-right: 1px solid var(--border)
        }

        .mini-stat:last-child {
            border-right: none
        }

        .mini-val {
            font-family: var(--mono);
            font-size: 22px;
            font-weight: 600;
            line-height: 1
        }

        .mini-lbl {
            font-size: 10px;
            color: var(--ink3);
            margin-top: 3px;
            text-transform: uppercase;
            letter-spacing: .5px
        }

        /* Liste présences */
        .presence-list {
            overflow-y: auto;
            max-height: 380px
        }

        .presence-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 20px;
            border-bottom: 1px solid var(--border);
            transition: background .15s
        }

        .presence-item:last-child {
            border-bottom: none
        }

        .presence-item:hover {
            background: var(--bg)
        }

        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            color: var(--ink2);
            border: 1px solid var(--border);
            flex-shrink: 0
        }

        .pres-name {
            font-size: 13px;
            font-weight: 500;
            color: var(--ink)
        }

        .pres-role {
            font-size: 11px;
            color: var(--ink3)
        }

        .pres-time {
            font-family: var(--mono);
            font-size: 12px;
            color: var(--ink2);
            margin-left: auto;
            text-align: right
        }

        .badge {
            display: inline-flex;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500
        }

        .badge-green {
            background: var(--emerald-bg);
            color: var(--emerald)
        }

        .badge-red {
            background: var(--crimson-bg);
            color: var(--crimson)
        }

        .badge-amber {
            background: var(--amber-bg);
            color: var(--amber)
        }

        /* Barre du bas */
        .bottom-bar {
            width: 100%;
            max-width: 900px;
            margin-top: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11.5px;
            color: var(--ink3)
        }

        .bottom-bar a {
            color: var(--ink3);
            text-decoration: none
        }

        .bottom-bar a:hover {
            color: var(--ink)
        }
    </style>
</head>

<body>

    <!-- Top bar -->
    <div class="top-bar">
        <div>
            <div class="company-name">{{ \App\Models\Parametre::valeur('nom_entreprise', 'RH Manager') }}</div>
            <div class="company-sub">Interface de pointage du personnel</div>
        </div>
        <div class="device-status">
            <div class="dot" id="device-dot" style="background:var(--ink4)"></div>
            <span id="device-txt">Vérification de l'appareil…</span>
        </div>
    </div>

    <!-- Layout 2 colonnes -->
    <div class="layout">

        <!-- COLONNE GAUCHE : Pointage -->
        <div class="card">
            <!-- Horloge -->
            <div class="clock" id="clock">--:--:--</div>
            <div class="clock-date" id="clock-date">Chargement…</div>

            <!-- Horaires configurés -->
            <div class="horaires">
                <div class="horaire-slot">
                    <div class="horaire-time">{{ \App\Models\Parametre::valeur('heure_arrivee', '08:00') }}</div>
                    <div class="horaire-lbl">Arrivée</div>
                </div>
                <div class="horaire-slot slot-retard">
                    <div class="horaire-time">{{ \App\Models\Parametre::valeur('heure_limite_retard', '08:30') }}</div>
                    <div class="horaire-lbl">Limite</div>
                </div>
                <div class="horaire-slot">
                    <div class="horaire-time">{{ \App\Models\Parametre::valeur('heure_sortie', '17:00') }}</div>
                    <div class="horaire-lbl">Sortie</div>
                </div>
            </div>

            <!-- Empreinte -->
            <div class="fp-wrap">
                <div class="fp-ring" id="fp-ring" onclick="simulerScan()">
                    <svg width="54" height="66" viewBox="0 0 60 72" fill="none" stroke="var(--ink3)"
                        stroke-width="1.3" id="fp-svg">
                        <path d="M30 6C18 6 8 15 8 27c0 12 6 27 14 38" stroke-width="1.6" />
                        <path d="M30 6c12 0 22 9 22 21 0 12-6 27-14 38" stroke-width="1.6" />
                        <path d="M18 21c0-7 5.4-12 12-12s12 5 12 12c0 11-6 23-12 32C24 44 18 32 18 21" />
                        <path d="M22 29c0-5 3.5-8.5 8-8.5s8 3.5 8 8.5c0 6.5-3 13-8 18" />
                        <path d="M26 36c0-3 1.5-5 4-5s4 2 4 5c0 3.5-2 6-4 8.5" />
                    </svg>
                    <div class="fp-scan" id="fp-scan" style="display:none"></div>
                </div>
                <div class="fp-status" id="fp-txt">Posez le doigt sur le capteur</div>
            </div>

            <!-- Boutons -->
            <div class="btn-grid">
                <button class="btn-point btn-entree" onclick="pointer('entree')">
                    <span class="btn-icon">→</span>
                    <span>Entrée</span>
                    <span class="btn-label">Pointer à l'arrivée</span>
                </button>
                <button class="btn-point btn-sortie" onclick="pointer('sortie')">
                    <span class="btn-icon">←</span>
                    <span>Sortie</span>
                    <span class="btn-label">Pointer au départ</span>
                </button>
            </div>

            <!-- Message résultat -->
            <div class="msg" id="msg">
                <div class="msg-name" id="msg-name"></div>
                <div class="msg-detail" id="msg-detail"></div>
            </div>
        </div>

        <!-- COLONNE DROITE : Liste présences du jour -->
        <div class="card-right">
            <div class="card-right-header">
                <div class="card-right-title">Présences du jour</div>
                <span id="date-courte" style="font-size:12px;color:var(--ink3)"></span>
            </div>

            <!-- Mini stats -->
            <div class="mini-stats">
                <div class="mini-stat">
                    <div class="mini-val" style="color:var(--emerald)" id="stat-presents">—</div>
                    <div class="mini-lbl">Présents</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-val" style="color:var(--amber)" id="stat-retards">—</div>
                    <div class="mini-lbl">Retards</div>
                </div>
                <div class="mini-stat">
                    <div class="mini-val" style="color:var(--crimson)" id="stat-absents">—</div>
                    <div class="mini-lbl">Absents</div>
                </div>
            </div>

            <!-- Liste -->
            <div class="presence-list" id="presence-list">
                <div style="padding:32px;text-align:center;color:var(--ink3);font-size:13px">
                    Chargement des présences…
                </div>
            </div>
        </div>
    </div>

    <!-- Barre du bas -->
    <div class="bottom-bar">
        <span>© {{ now()->year }} {{ \App\Models\Parametre::valeur('nom_entreprise', 'RH Manager') }}</span>
        <a href="{{ route('login') }}">Accès administrateur →</a>
    </div>

    <script>
        const CSRF = document.querySelector('meta[name=csrf-token]').content;
        const LIMITE = '{{ \App\Models\Parametre::valeur('heure_limite_retard', '08:30') }}';

        // ── Horloge ──────────────────────────────────────────────────────
        const jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        const mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre',
            'novembre', 'décembre'
        ];

        function majHorloge() {
            const now = new Date();
            document.getElementById('clock').textContent = [now.getHours(), now.getMinutes(), now.getSeconds()].map(n =>
                String(n).padStart(2, '0')).join(':');
            const dateStr = `${jours[now.getDay()]} ${now.getDate()} ${mois[now.getMonth()]} ${now.getFullYear()}`;
            document.getElementById('clock-date').textContent = dateStr;
            document.getElementById('date-courte').textContent = `${now.getDate()} ${mois[now.getMonth()]}`;
        }
        majHorloge();
        setInterval(majHorloge, 1000);

        // ── Statut appareil ───────────────────────────────────────────────
        fetch('/pointage/statut')
            .then(r => r.json())
            .then(d => {
                const dot = document.getElementById('device-dot');
                const txt = document.getElementById('device-txt');
                if (d.connecte) {
                    dot.className = 'dot dot-green';
                    txt.textContent = 'Appareil biométrique connecté';
                } else {
                    dot.className = 'dot dot-amber';
                    txt.textContent = 'Mode manuel (appareil non connecté)';
                }
            })
            .catch(() => {
                document.getElementById('device-txt').textContent = 'Statut non disponible';
            });

        // ── Scanner simulation ────────────────────────────────────────────
        let scanning = false;

        function simulerScan() {
            if (scanning) return;
            scanning = true;
            const ring = document.getElementById('fp-ring');
            const scan = document.getElementById('fp-scan');
            const txt = document.getElementById('fp-txt');
            ring.className = 'fp-ring scanning';
            scan.style.display = '';
            txt.textContent = 'Lecture en cours…';
            txt.className = 'fp-status';

            setTimeout(() => {
                ring.className = 'fp-ring success';
                scan.style.display = 'none';
                txt.textContent = '✓ Empreinte reconnue';
                txt.className = 'fp-status success';
                scanning = false;
            }, 1800);
        }

        // ── Pointer ───────────────────────────────────────────────────────
        function pointer(type) {
            const now = new Date();
            const hh = String(now.getHours()).padStart(2, '0');
            const mm = String(now.getMinutes()).padStart(2, '0');
            const limiteParts = LIMITE.split(':');
            const retard = type === 'entree' &&
                (now.getHours() > parseInt(limiteParts[0]) ||
                    (now.getHours() == parseInt(limiteParts[0]) && now.getMinutes() > parseInt(limiteParts[1])));

            fetch('/pointage/empreinte', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({
                        empreinte_id: 'DEMO',
                        type
                    })
                })
                .then(r => r.json())
                .then(afficherMessage.bind(null, type, hh + ':' + mm, retard))
                .catch(() => afficherMessage(type, hh + ':' + mm, retard, {
                    succes: true,
                    message: '',
                    employe: ''
                }));

            // Refresh de la liste après 1s
            setTimeout(chargerPresences, 1200);
        }

        function afficherMessage(type, heure, retard, data) {
            const msg = document.getElementById('msg');
            const name = document.getElementById('msg-name');
            const detail = document.getElementById('msg-detail');

            const cls = retard && type === 'entree' ? 'warning' : (data.succes !== false ? 'success' : 'error');
            const icon = type === 'entree' ? '👋' : '🚪';
            const texte = type === 'entree' ?
                `${icon} Bienvenue !${retard ? '  —  Retard enregistré' : ''}` :
                `${icon} Au revoir !`;

            name.textContent = texte;
            detail.textContent = `${type === 'entree' ? 'Entrée' : 'Sortie'} à ${heure}`;
            msg.className = `msg ${cls}`;
            msg.style.display = '';

            // Auto-hide après 5 secondes
            setTimeout(() => {
                msg.style.display = 'none';
            }, 5000);
        }

        // ── Charger liste présences ───────────────────────────────────────
        function chargerPresences() {
            fetch('/admin/presences?json=1', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .catch(() => null)
                .then(r => r ? r.json() : null)
                .then(data => {
                    if (!data) {
                        afficherPresencesMock();
                        return;
                    }
                    renderPresences(data);
                });
        }

        // Données mock pour affichage sans API
        function afficherPresencesMock() {
            const list = document.getElementById('presence-list');
            list.innerHTML = `
    <div style="padding:20px 20px 0;color:var(--ink3);font-size:12px;font-style:italic">
      Connectez-vous pour voir les présences en temps réel.
    </div>
    <div class="presence-item">
      <div class="avatar">MJ</div>
      <div><div class="pres-name">Mbeki Jean</div><div class="pres-role">Directeur RH</div></div>
      <div class="pres-time" style="text-align:right">
        <div>07:58 → 17:02</div>
        <span class="badge badge-green">Présent</span>
      </div>
    </div>
    <div class="presence-item">
      <div class="avatar">KS</div>
      <div><div class="pres-name">Kabila Solange</div><div class="pres-role">Comptable</div></div>
      <div class="pres-time" style="text-align:right">
        <div>08:35 → 17:05</div>
        <span class="badge badge-amber">Retard</span>
      </div>
    </div>
    <div class="presence-item">
      <div class="avatar">TP</div>
      <div><div class="pres-name">Tshisekedi Paul</div><div class="pres-role">Ingénieur</div></div>
      <div class="pres-time">
        <span class="badge badge-red">Absent</span>
      </div>
    </div>`;

            document.getElementById('stat-presents').textContent = '4';
            document.getElementById('stat-retards').textContent = '1';
            document.getElementById('stat-absents').textContent = '2';
        }

        chargerPresences();

        function renderPresences(data) {
            document.getElementById('stat-presents').textContent = data.stats?.presents ?? '—';
            document.getElementById('stat-retards').textContent = data.stats?.retards ?? '—';
            document.getElementById('stat-absents').textContent = data.stats?.absents ?? '—';

            const list = document.getElementById('presence-list');
            if (!data.presences?.length) {
                list.innerHTML =
                    '<div style="padding:32px;text-align:center;color:var(--ink3);font-size:13px">Aucun pointage enregistré aujourd\'hui.</div>';
                return;
            }

            list.innerHTML = data.presences.map(p => `
    <div class="presence-item">
      <div class="avatar">${p.initiales}</div>
      <div>
        <div class="pres-name">${p.nom}</div>
        <div class="pres-role">${p.fonction}</div>
      </div>
      <div class="pres-time">
        ${p.heure_entree ? `<div>${p.heure_entree}${p.heure_sortie?' → '+p.heure_sortie:''}</div>` : ''}
        <span class="badge badge-${p.statut==='present'?'green':p.statut==='retard'?'amber':'red'}">
          ${p.statut==='present'?'Présent':p.statut==='retard'?'Retard':'Absent'}
        </span>
      </div>
    </div>`).join('');
        }
    </script>
</body>

</html>
