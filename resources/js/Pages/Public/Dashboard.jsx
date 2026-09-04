import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';

const tabs = { total: 'Geral', efficiency: 'Eficiência', precision: 'Precisão', aesthetic: 'Estética' };
const numberFormatter = new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 2 });

function isNumeric(value) {
    return value !== null && value !== undefined && value !== '' && Number.isFinite(Number(value));
}

function formatNumber(value) {
    return isNumeric(value) ? numberFormatter.format(Number(value)) : '—';
}

function formatKg(value) {
    return isNumeric(value) ? `${formatNumber(value)} kg` : '—';
}

function formatSeconds(value) {
    return isNumeric(value) ? `${formatNumber(value)} s` : '—';
}

function categoryLabel(category) {
    return category === 'civil' ? 'Engenharia Civil' : 'Ampla concorrência';
}

function voteLabel(count) {
    const votes = Number(count) || 0;
    return `${votes} ${votes === 1 ? 'voto' : 'votos'} de estética`;
}

export default function AudienceDashboard({ ranking = [], currentTest, completedJudges = 0 }) {
    const [metric, setMetric] = useState('total');
    const [expanded, setExpanded] = useState(null);
    useEffect(() => {
        const timer = window.setInterval(() => router.reload({
            only: ['ranking', 'currentTest', 'completedJudges'],
            preserveScroll: true,
        }), 1000);

        return () => window.clearInterval(timer);
    }, []);

    const displayed = useMemo(
        () => [...ranking].sort((a, b) => Number(b[metric] ?? 0) - Number(a[metric] ?? 0) || a.position - b.position),
        [ranking, metric],
    );

    const updateText = `Atualização automática · ${voteLabel(completedJudges)}`;

    return <div className="audience-scoreboard">
        <Head title="Placar oficial — Concurso de Pontes de Palito">
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
            <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
        </Head>

        <header className="scoreboard-header">
            <Link href={route('registration.create')}>
                <img src="/images/logo-ponte.png" alt="" />
                <span><strong>Concurso de Pontes de Palito</strong><small>Placar oficial</small></span>
            </Link>
            <span className="scoreboard-header-live"><i aria-hidden="true" /> Ao vivo</span>
        </header>

        <main className="scoreboard-page">
            <header className="scoreboard-hero">
                <span className="eyebrow">Resultados da competição</span>
                <h1>Classificação</h1>
                <p role="status" aria-live="polite">{updateText}</p>
            </header>

            <section className="scoreboard-section live-test-section" aria-labelledby="live-test-title">
                <SectionHeading id="live-test-title" label="Ensaio ao vivo" description="Telemetria da ponte em teste" />
                <CurrentTest test={currentTest} />
            </section>

            <section className="scoreboard-section ranking-section" aria-labelledby="ranking-title">
                <SectionHeading id="ranking-title" label="Classificação atual" description="Resultados consolidados" />
                <div className="ranking-toolbar">
                    <div className="ranking-tabs" aria-label="Ordenar placar">
                        {Object.entries(tabs).map(([value, label]) => <button
                            key={value}
                            type="button"
                            onClick={() => setMetric(value)}
                            className={metric === value ? 'is-active' : ''}
                            aria-pressed={metric === value}
                        >{label}</button>)}
                    </div>
                </div>
                <div className="ranking-list">
                    {displayed.map((team, index) => <RankingCard
                        key={team.id}
                        team={team}
                        displayPosition={index + 1}
                        metric={metric}
                        expanded={expanded === team.id}
                        onToggle={() => setExpanded(expanded === team.id ? null : team.id)}
                    />)}
                    {!displayed.length && <div className="empty-ranking">O ranking aparecerá quando houver pontes aprovadas na auditoria.</div>}
                </div>
            </section>
        </main>

        <footer className="scoreboard-footer"><strong>Construção Civil · IFSP Caraguatatuba</strong><Link href={route('event.regulation')}>Regulamento</Link></footer>
    </div>;
}

function SectionHeading({ id, label, description }) {
    return <div className="section-heading-compact"><div><span aria-hidden="true" /><h2 id={id}>{label}</h2></div><p>{description}</p></div>;
}

function CurrentTest({ test }) {
    if (!test) {
        return <article className="current-test-empty"><span className="test-pulse is-paused" aria-hidden="true" /><div><strong>Aguardando próximo ensaio</strong><p>A classificação permanece disponível.</p></div></article>;
    }

    return <div className="current-test">
        <article className="current-test-card">
            <span className="test-pulse" aria-hidden="true" />
            <div className="current-test-identity"><small>Agora no ensaio</small><strong title={test.name}>{test.name}</strong><span>Categoria · {categoryLabel(test.category)}</span></div>
            <p><small>Carga declarada</small><b>{formatKg(test.declaredLoadKg)}</b></p>
        </article>
        <RuptureChart samples={test.samples} declaredLoad={test.declaredLoadKg} title="Carga ao vivo" compact />
    </div>;
}

function RankingCard({ team, displayPosition, metric, expanded, onToggle }) {
    const podium = displayPosition <= 3;
    const panelId = `team-details-${team.id}`;
    const ruptureTime = getRuptureTime(team.ruptureSamples);
    const difference = loadDifference(team.actualLoadKg, team.declaredLoadKg);

    return <article className={`ranking-card ${podium ? `is-podium is-position-${displayPosition}` : ''} ${expanded ? 'is-expanded' : ''}`}>
        <div className="ranking-card-main">
            <span className="ranking-position">{displayPosition}<sup>º</sup></span>
            <div className="ranking-team">{podium && <span className="podium-label">{displayPosition === 1 ? 'Liderança' : 'Pódio'}</span>}<h3 title={team.name}>{team.name}</h3><p>{categoryLabel(team.category)}</p></div>
            <div className="ranking-score"><strong>{formatNumber(team[metric])}</strong><span>{metric === 'total' ? 'pontos' : tabs[metric]}</span></div>
        </div>
        <button type="button" onClick={onToggle} className="details-button" aria-expanded={expanded} aria-controls={panelId}>
            <span>{expanded ? 'Ocultar detalhes' : 'Ver detalhes'}</span><svg aria-hidden="true" viewBox="0 0 20 20"><path d="m5.5 7.5 4.5 4 4.5-4" /></svg>
        </button>
        {expanded && <div className="rupture-panel" id={panelId}>
            <div className="rupture-intro"><span className="eyebrow">Dados do ensaio</span><h4>Resumo da ruptura</h4></div>

            <RuptureChart samples={team.ruptureSamples} rupturePoint={team.actualLoadKg} declaredLoad={team.declaredLoadKg} title="Histórico carga × tempo" />

            <div className="detail-groups">
                <section aria-labelledby={`${panelId}-scores`}><h5 id={`${panelId}-scores`}>Pontuação</h5><div className="metric-grid score-metrics"><Metric label="Eficiência (EE)" value={formatNumber(team.efficiency)} /><Metric label="Precisão (PP)" value={formatNumber(team.precision)} /><Metric label="Estética" value={formatNumber(team.aesthetic)} /><Metric label="Total de pontos" value={formatNumber(team.total)} /></div></section>
                <section aria-labelledby={`${panelId}-rupture`}><h5 id={`${panelId}-rupture`}>Dados da ruptura</h5><div className="metric-grid rupture-metrics"><Metric label="Carga máxima" value={formatKg(team.actualLoadKg)} /><Metric label="Tempo de ruptura" value={formatSeconds(ruptureTime)} /><Metric label="Carga declarada" value={formatKg(team.declaredLoadKg)} /><Metric label="Diferença declarada" value={difference.label} detail={difference.detail} /></div></section>
            </div>

            {team.members?.length > 0 && <div className="team-members"><strong>Integrantes</strong><ul>{team.members.map((member, index) => <li key={`${member.name}-${index}`}><span>{member.name}</span><small>{member.course}</small></li>)}</ul></div>}
        </div>}
    </article>;
}

function Metric({ label, value, detail }) {
    return <div className="metric-item"><strong>{value}</strong><span>{label}</span>{detail && <small>{detail}</small>}</div>;
}

function loadDifference(actual, declared) {
    if (!isNumeric(actual) || !isNumeric(declared)) return { label: '—', detail: 'Dados insuficientes' };
    const difference = Number(actual) - Number(declared);
    if (Math.abs(difference) < 0.005) return { label: '0 kg', detail: 'Igual à carga declarada' };
    return {
        label: `${difference > 0 ? '+' : '−'}${formatNumber(Math.abs(difference))} kg`,
        detail: `${difference > 0 ? 'Acima' : 'Abaixo'} da carga declarada`,
    };
}

function getRuptureTime(samples = []) {
    const validSamples = samples.filter((sample) => Number.isFinite(Number(sample.seconds)) && Number.isFinite(Number(sample.loadKg)));
    if (!validSamples.length) return null;
    return validSamples.reduce((peak, sample) => Number(sample.loadKg) > Number(peak.loadKg) ? sample : peak, validSamples[0]).seconds;
}

function niceDomainMaximum(value, tickCount = 4) {
    if (!Number.isFinite(value) || value <= 0) return tickCount;
    const roughStep = value / tickCount;
    const magnitude = 10 ** Math.floor(Math.log10(roughStep));
    const normalized = roughStep / magnitude;
    const niceNormalized = normalized <= 1 ? 1 : normalized <= 2 ? 2 : normalized <= 5 ? 5 : 10;
    const step = niceNormalized * magnitude;
    return Math.ceil(value / step) * step;
}

function makeTicks(maximum, count = 4) {
    return Array.from({ length: count + 1 }, (_, index) => (maximum / count) * index);
}

function RuptureChart({ samples = [], rupturePoint, declaredLoad, title, compact = false }) {
    const chart = useMemo(() => {
        const validSamples = samples
            .map((sample) => ({ seconds: Number(sample.seconds), loadKg: Number(sample.loadKg) }))
            .filter((sample) => Number.isFinite(sample.seconds) && Number.isFinite(sample.loadKg))
            .sort((a, b) => a.seconds - b.seconds);
        const isEstimated = !validSamples.length && isNumeric(rupturePoint) && Number(rupturePoint) > 0;
        const estimatedPeak = Number(rupturePoint);
        const effectiveSamples = validSamples.length ? validSamples : isEstimated ? [
            { seconds: 0, loadKg: 0 }, { seconds: 6, loadKg: estimatedPeak * .10 },
            { seconds: 12, loadKg: estimatedPeak * .24 }, { seconds: 18, loadKg: estimatedPeak * .34 },
            { seconds: 24, loadKg: estimatedPeak * .52 }, { seconds: 30, loadKg: estimatedPeak * .63 },
            { seconds: 36, loadKg: estimatedPeak * .79 }, { seconds: 42, loadKg: estimatedPeak },
            { seconds: 44, loadKg: estimatedPeak * .05 }, { seconds: 50, loadKg: 0 },
        ] : [];

        if (!effectiveSamples.length) return null;

        const width = 360;
        const height = compact ? 226 : 218;
        const plot = { left: 47, top: compact ? 37 : 31, right: 340, bottom: compact ? 184 : 176 };
        const peak = effectiveSamples.reduce((highest, sample) => sample.loadKg > highest.loadKg ? sample : highest, effectiveSamples[0]);
        const last = effectiveSamples[effectiveSamples.length - 1];
        const maxObservedTime = Math.max(...effectiveSamples.map((sample) => sample.seconds), 1);
        const maxObservedLoad = Math.max(...effectiveSamples.map((sample) => sample.loadKg), Number(declaredLoad) || 0, Number(rupturePoint) || 0, 1);
        const maxTime = niceDomainMaximum(maxObservedTime, 4);
        const maxLoad = niceDomainMaximum(maxObservedLoad * 1.12, 4);
        const x = (seconds) => plot.left + (seconds / maxTime) * (plot.right - plot.left);
        const y = (loadKg) => plot.bottom - (loadKg / maxLoad) * (plot.bottom - plot.top);
        const points = effectiveSamples.length === 1
            ? `${x(0)},${y(effectiveSamples[0].loadKg)} ${x(effectiveSamples[0].seconds || maxTime * .02)},${y(effectiveSamples[0].loadKg)}`
            : effectiveSamples.map((sample) => `${x(sample.seconds)},${y(sample.loadKg)}`).join(' ');

        return { width, height, plot, maxTime, maxLoad, isEstimated, peak, last, x, y, points, xTicks: makeTicks(maxTime), yTicks: makeTicks(maxLoad) };
    }, [samples, rupturePoint, declaredLoad, compact]);

    if (!chart) return <div className="rupture-unavailable" role="status"><strong>Curva de carga indisponível</strong><span>Aguardando medições suficientes deste ensaio.</span>{isNumeric(rupturePoint) && <b>Ponto de ruptura: {formatKg(rupturePoint)}</b>}</div>;

    const marker = compact ? chart.last : chart.peak;
    const markerX = chart.x(marker.seconds);
    const markerY = chart.y(marker.loadKg);
    const declaredY = isNumeric(declaredLoad) && Number(declaredLoad) > 0 && Number(declaredLoad) <= chart.maxLoad ? chart.y(Number(declaredLoad)) : null;
    const labelAnchor = markerX > chart.plot.right - 78 ? 'end' : 'start';
    const labelX = markerX + (labelAnchor === 'end' ? -8 : 8);
    const labelY = Math.max(markerY - 10, chart.plot.top + 12);

    return <figure className={`rupture-chart ${compact ? 'is-compact' : ''} ${chart.isEstimated ? 'is-estimated' : ''}`}>
        <figcaption><span>{title}{chart.isEstimated && <em>Curva reconstruída</em>}</span>{!compact && isNumeric(rupturePoint) && <strong>Ponto de ruptura: {formatKg(rupturePoint)}</strong>}</figcaption>
        <svg viewBox={`0 0 ${chart.width} ${chart.height}`} role="img" aria-label={`${title}: carga em quilogramas ao longo do tempo em segundos`}>
            <g className="chart-grid">{chart.yTicks.map((tick) => <line key={`y-${tick}`} x1={chart.plot.left} y1={chart.y(tick)} x2={chart.plot.right} y2={chart.y(tick)} />)}</g>
            <g className="chart-ticks">
                {chart.yTicks.map((tick) => <text key={`yl-${tick}`} x={chart.plot.left - 7} y={chart.y(tick) + 3} textAnchor="end">{formatNumber(tick)} kg</text>)}
                {chart.xTicks.map((tick) => <text key={`xl-${tick}`} x={chart.x(tick)} y={chart.plot.bottom + 18} textAnchor="middle">{formatNumber(tick)} s</text>)}
            </g>
            <path className="chart-axis" d={`M${chart.plot.left} ${chart.plot.top}V${chart.plot.bottom}H${chart.plot.right}`} />
            {declaredY !== null && <g className="declared-load-line"><line x1={chart.plot.left} y1={declaredY} x2={chart.plot.right} y2={declaredY} /><text x={chart.plot.left + 6} y={declaredY - 5}>Declarada: {formatKg(declaredLoad)}</text></g>}
            <polyline className="chart-line" points={chart.points} strokeDasharray={chart.isEstimated ? '7 5' : undefined} />
            {!compact && <line className="rupture-marker-line" x1={markerX} y1={chart.plot.top} x2={markerX} y2={chart.plot.bottom} />}
            <circle className="chart-marker" cx={markerX} cy={markerY} r="5" />
            <g className="chart-current-label"><text x={labelX} y={labelY} textAnchor={labelAnchor}>{formatKg(marker.loadKg)}</text><text x={labelX} y={labelY + 12} textAnchor={labelAnchor}>{formatSeconds(marker.seconds)}</text></g>
        </svg>
        {chart.isEstimated && <p className="chart-source-note">Curva reconstruída a partir da carga final; a linha tracejada não representa medições registradas.</p>}
    </figure>;
}
