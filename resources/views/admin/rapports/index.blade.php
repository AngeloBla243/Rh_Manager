@extends('layouts.admin')

@section('title', 'Rapports & Statistiques')
@section('page-title', 'Rapports & Statistiques')
@section('page-sub', 'Analyses de présence, paie et performance RH')

@section('topbar-actions')
    <select class="form-control" style="width:auto" onchange="window.location='?annee='+this.value">
        @for ($y = now()->year; $y >= now()->year - 3; $y--)
            <option value="{{ $y }}" {{ ($annee ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}
            </option>
        @endfor
    </select>

    <a href="{{ route('admin.pdf.rapport-presences', ['mois' => now()->month, 'annee' => $annee ?? now()->year]) }}"
        class="btn btn-primary" target="_blank">📊 Exporter PDF</a>
@endsection

@section('content')
    @php
        $moisLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        $tauxAnnuelValue = $tauxAnnuel ?? 0;
        $couleurTauxAnnuel =
            $tauxAnnuelValue >= 85 ? 'var(--emerald)' : ($tauxAnnuelValue >= 70 ? 'var(--amber)' : 'var(--crimson)');
        $anneeCourante = $annee ?? now()->year;
    @endphp

    {{-- KPIs globaux --}}
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-label">Taux présence moyen</div>
            <div class="stat-value" style="color:{{ $couleurTauxAnnuel }}">
                {{ round($tauxAnnuelValue, 1) }}%
            </div>
            <div class="stat-sub">Année {{ $anneeCourante }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Taux absentéisme</div>
            <div class="stat-value" style="color:var(--crimson)">
                {{ round(100 - $tauxAnnuelValue, 1) }}%
            </div>
            <div class="stat-sub">{{ $absencesAnnee ?? 0 }} jours d'absence</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Dépenses salariales</div>
            <div class="stat-value money">{{ number_format($stats['total_annuel'] ?? 0, 0, ',', ' ') }} $</div>
            <div class="stat-sub">
                Primes : {{ number_format($stats['total_primes'] ?? 0, 0, ',', ' ') }} $
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Heures supplémentaires</div>
            <div class="stat-value money" style="color:var(--amber)">
                {{ number_format($stats['total_heures_sup'] ?? 0, 0, ',', ' ') }} $
            </div>
            <div class="stat-sub">
                Pénalités : -{{ number_format($stats['total_penalites'] ?? 0, 0, ',', ' ') }} $
            </div>
        </div>
    </div>

    {{-- Graphiques --}}
    <div class="grid grid-2 mb-4">
        {{-- Taux de présence mensuel --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">Taux de présence mensuel</div>
            </div>

            <div class="card-body" id="chart-presence">
                <div style="display:flex;align-items:flex-end;gap:5px;height:120px">
                    @php
                        $presences = $stats['depenses_par_mois'] ?? array_fill(0, 12, ['taux_moyen' => 0]);
                    @endphp

                    @foreach ($presences as $i => $d)
                        @php
                            $taux = round($d['taux_moyen'] ?? 0, 1);
                            $isCurrent = $i + 1 == now()->month;
                            $couleurBarre =
                                $taux >= 85
                                    ? 'var(--emerald)'
                                    : ($taux >= 70
                                        ? 'var(--amber)'
                                        : ($taux > 0
                                            ? 'var(--crimson)'
                                            : 'var(--bg3)'));
                        @endphp

                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:3px">
                            <div style="font-size:9px;color:var(--ink3);font-family:var(--mono)">
                                {{ $taux > 0 ? $taux . '%' : '' }}
                            </div>
                            <div
                                style="width:100%;border-radius:3px 3px 0 0;min-height:4px;
                                height:{{ max(4, $taux) }}px;
                                background:{{ $couleurBarre }};
                                opacity:{{ $isCurrent ? '1' : '.65' }}">
                            </div>
                            <div
                                style="font-size:9.5px;color:{{ $isCurrent ? 'var(--ink)' : 'var(--ink3)' }};font-weight:{{ $isCurrent ? '600' : '400' }}">
                                {{ $moisLabels[$i] }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="display:flex;gap:12px;margin-top:12px;flex-wrap:wrap">
                    @foreach ([['var(--emerald)', '≥ 85% Excellent'], ['var(--amber)', '70–84% Moyen'], ['var(--crimson)', '< 70% Insuffisant']] as [$c, $l])
                        <div style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--ink2)">
                            <div
                                style="width:10px;height:10px;border-radius:2px;background:{{ $c }};flex-shrink:0">
                            </div>
                            {{ $l }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Évolution dépenses salariales --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">Évolution des dépenses salariales ($)</div>
            </div>

            <div class="card-body">
                @php
                    $maxNet = max(1, max(array_column($stats['depenses_par_mois'] ?? [['net' => 1]], 'net') ?: [1]));
                @endphp

                <div style="display:flex;align-items:flex-end;gap:5px;height:120px">
                    @foreach ($stats['depenses_par_mois'] ?? array_fill(0, 12, []) as $i => $d)
                        @php
                            $net = $d['net'] ?? 0;
                            $h = $net > 0 ? max(4, round(($net / $maxNet) * 100)) : 3;
                        @endphp

                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:3px">
                            @if ($net > 0)
                                <div style="font-size:8px;color:var(--ink3);font-family:var(--mono)">
                                    {{ number_format($net / 1000, 0) }}k
                                </div>
                            @endif

                            <div
                                style="width:100%;border-radius:3px 3px 0 0;min-height:3px;height:{{ $h }}px;background:var(--cobalt-bg)">
                            </div>
                            <div style="font-size:9.5px;color:var(--ink3)">{{ $moisLabels[$i] }}</div>
                        </div>
                    @endforeach
                </div>

                <div style="margin-top:10px;font-size:11.5px;color:var(--ink2)">
                    Total annuel :
                    <strong class="font-mono" style="color:var(--cobalt)">
                        {{ number_format($stats['total_annuel'] ?? 0, 0, ',', ' ') }} $
                    </strong>
                    dont primes
                    <strong class="font-mono">
                        {{ number_format($stats['total_primes'] ?? 0, 0, ',', ' ') }} $
                    </strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Classement présence employés --}}
    <div class="card mb-4">
        <div class="card-header">
            <div class="card-title">Classement des employés — Taux de présence {{ $anneeCourante }}</div>
            <div class="text-sm text-muted">Trié par performance décroissante</div>
        </div>

        <div class="card-body" style="display:flex;flex-direction:column;gap:14px">
            @forelse($tauxParEmploye ?? [] as $i => $ligne)
                @php
                    $taux = $ligne['taux'] ?? 0;
                    $couleurLigne = $taux >= 85 ? 'var(--emerald)' : ($taux >= 70 ? 'var(--amber)' : 'var(--crimson)');
                @endphp

                <div style="display:flex;align-items:center;gap:12px">
                    <div
                        style="font-family:var(--mono);font-size:13px;font-weight:700;color:var(--ink3);min-width:20px;text-align:center">
                        {{ $i < 3 ? ['🥇', '🥈', '🥉'][$i] : $i + 1 }}
                    </div>

                    <div class="avatar">{{ $ligne['employe']->initiales }}</div>

                    <div style="flex:1">
                        <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:4px">
                            <div>
                                <span style="font-weight:500;font-size:13px">
                                    {{ $ligne['employe']->nom }} {{ $ligne['employe']->prenom }}
                                </span>
                                <span class="text-sm text-muted" style="margin-left:8px">
                                    {{ $ligne['employe']->fonction }}
                                </span>
                            </div>

                            <div style="display:flex;align-items:center;gap:10px">
                                <span class="text-xs text-muted font-mono">
                                    {{ $ligne['jours_travailles'] }} jrs / {{ $ligne['nb_absences'] }} abs
                                </span>
                                <strong class="font-mono" style="font-size:14px;color:{{ $couleurLigne }}">
                                    {{ $taux }}%
                                </strong>
                            </div>
                        </div>

                        <div style="height:7px;background:var(--bg3);border-radius:4px;overflow:hidden">
                            <div
                                style="height:100%;border-radius:4px;transition:width .6s ease;
                                width:{{ $taux }}%;
                                background:{{ $couleurLigne }}">
                            </div>
                        </div>

                        <div style="display:flex;gap:14px;margin-top:4px">
                            <span class="text-xs text-muted">{{ $ligne['nb_retards'] ?? 0 }} retard(s)</span>
                            <span
                                class="text-xs text-muted font-mono">{{ number_format($ligne['salaire_annuel'] ?? 0, 0, ',', ' ') }}
                                $ versés</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty">
                    <div class="empty-title">Données insuffisantes</div>
                    <div class="empty-sub">Calculez les salaires pour générer les statistiques.</div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Tableau récapitulatif mensuel --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">Récapitulatif mensuel — {{ $anneeCourante }}</div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Mois</th>
                        <th>Salaires bruts</th>
                        <th>Primes</th>
                        <th>H. sup.</th>
                        <th>Pénalités</th>
                        <th>Net versé</th>
                        <th>Taux présence</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($stats['depenses_par_mois'] ?? array_fill(0, 12, []) as $i => $d)
                        @php
                            $isCurrent = $i + 1 == now()->month;
                            $tauxMois = $d['taux_moyen'] ?? 0;
                            $couleurTauxMois =
                                $tauxMois >= 85
                                    ? 'var(--emerald)'
                                    : ($tauxMois >= 70
                                        ? 'var(--amber)'
                                        : 'var(--crimson)');
                        @endphp

                        <tr {{ $isCurrent ? 'style=background:var(--bg2)' : '' }}>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px">
                                    {{ ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'][$i] }}
                                    @if ($isCurrent)
                                        <span class="badge badge-blue" style="font-size:9px">En cours</span>
                                    @endif
                                </div>
                            </td>

                            <td class="font-mono">
                                {{ ($d['brut'] ?? 0) > 0 ? number_format($d['brut'], 0, ',', ' ') . ' $' : '—' }}</td>

                            <td class="font-mono"
                                style="color:{{ ($d['primes'] ?? 0) > 0 ? 'var(--emerald)' : 'var(--ink3)' }}">
                                {{ ($d['primes'] ?? 0) > 0 ? '+ ' . number_format($d['primes'], 0, ',', ' ') . ' $' : '—' }}
                            </td>

                            <td class="font-mono"
                                style="color:{{ ($d['heures_sup'] ?? 0) > 0 ? 'var(--amber)' : 'var(--ink3)' }}">
                                {{ ($d['heures_sup'] ?? 0) > 0 ? '+ ' . number_format($d['heures_sup'], 0, ',', ' ') . ' $' : '—' }}
                            </td>

                            <td class="font-mono"
                                style="color:{{ ($d['penalites'] ?? 0) > 0 ? 'var(--crimson)' : 'var(--ink3)' }}">
                                {{ ($d['penalites'] ?? 0) > 0 ? '- ' . number_format($d['penalites'], 0, ',', ' ') . ' $' : '—' }}
                            </td>

                            <td class="font-mono" style="font-weight:600;color:var(--ink)">
                                {{ ($d['net'] ?? 0) > 0 ? number_format($d['net'], 0, ',', ' ') . ' $' : '—' }}
                            </td>

                            <td>
                                @if ($tauxMois > 0)
                                    <div style="display:flex;align-items:center;gap:8px">
                                        <div
                                            style="width:60px;height:5px;background:var(--bg3);border-radius:3px;overflow:hidden">
                                            <div
                                                style="height:100%;border-radius:3px;width:{{ $tauxMois }}%;background:{{ $couleurTauxMois }}">
                                            </div>
                                        </div>
                                        <span class="font-mono text-sm">{{ round($tauxMois, 1) }}%</span>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <tr style="background:var(--ink);color:white">
                        <td style="padding:9px 14px;font-weight:600">TOTAL</td>
                        @php
                            $totals = array_reduce(
                                $stats['depenses_par_mois'] ?? [],
                                function ($c, $d) {
                                    return [
                                        'brut' => ($c['brut'] ?? 0) + ($d['brut'] ?? 0),
                                        'primes' => ($c['primes'] ?? 0) + ($d['primes'] ?? 0),
                                        'heures_sup' => ($c['heures_sup'] ?? 0) + ($d['heures_sup'] ?? 0),
                                        'penalites' => ($c['penalites'] ?? 0) + ($d['penalites'] ?? 0),
                                        'net' => ($c['net'] ?? 0) + ($d['net'] ?? 0),
                                    ];
                                },
                                [],
                            );
                        @endphp
                        <td class="font-mono" style="padding:9px 14px;color:#d1d5db">
                            {{ number_format($totals['brut'] ?? 0, 0, ',', ' ') }} $</td>
                        <td class="font-mono" style="padding:9px 14px;color:#6ee7b7">
                            {{ number_format($totals['primes'] ?? 0, 0, ',', ' ') }} $</td>
                        <td class="font-mono" style="padding:9px 14px;color:#fcd34d">
                            {{ number_format($totals['heures_sup'] ?? 0, 0, ',', ' ') }} $</td>
                        <td class="font-mono" style="padding:9px 14px;color:#fca5a5">
                            {{ number_format($totals['penalites'] ?? 0, 0, ',', ' ') }} $</td>
                        <td class="font-mono" style="padding:9px 14px;color:#6ee7b7;font-weight:700;font-size:15px">
                            {{ number_format($totals['net'] ?? 0, 0, ',', ' ') }} $</td>
                        <td style="padding:9px 14px;color:#d1d5db">{{ round($tauxAnnuelValue, 1) }}%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
