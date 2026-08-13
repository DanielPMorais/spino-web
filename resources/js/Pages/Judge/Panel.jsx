import { Head, useForm } from '@inertiajs/react';

export default function JudgePanel({ judge, teams, votingOpen, vote }) {
    const form = useForm({ first_team_id: '', second_team_id: '', third_team_id: '' });
    const submitted = Boolean(vote);
    const submit = (event) => {
        event.preventDefault();
        if (window.confirm('Confirmar este ranking? O voto não poderá ser alterado depois do envio.')) {
            form.post(route('judge.vote.store', judge.credential), { preserveScroll: true });
        }
    };

    return <><Head title="Painel do juiz" /><div className="min-h-screen bg-[#d1d1d1] text-[#1c2d3b]"><header className="bg-[#172938] px-5 py-6 text-center text-white"><h1 className="text-2xl font-bold tracking-tight">PAINEL DO JUIZ</h1><p className="mt-1 text-xs text-slate-300">Avaliação estética das pontes</p></header><main className="mx-auto w-full max-w-md px-5 py-10"><section className="rounded-xl bg-white px-5 py-4 shadow-sm"><p className="text-lg"><strong>Juiz:</strong> {judge.name}</p></section>{submitted ? <VoteReceipt vote={vote} /> : <form onSubmit={submit} className="mt-7 grid gap-5"><VotingNotice votingOpen={votingOpen} teamCount={teams.length} />{[['first_team_id', '1º LUGAR', '15 PONTOS'], ['second_team_id', '2º LUGAR', '10 PONTOS'], ['third_team_id', '3º LUGAR', '5 PONTOS']].map(([field, place, points]) => <RankingSelect key={field} field={field} place={place} points={points} teams={teams} form={form} />)}{form.errors.voting && <p className="rounded-lg bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">{form.errors.voting}</p>}<button disabled={!votingOpen || teams.length < 3 || form.processing} className="mt-1 min-h-12 rounded-xl bg-emerald-600 px-4 text-base font-bold text-white shadow-md transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-400">{form.processing ? 'ENVIANDO…' : 'CONFIRMAR VOTO ÚNICO'}</button><p className="text-center text-xs font-medium text-slate-600">Após confirmar, seu ranking não poderá ser alterado.</p></form>}</main></div></>;
}

function RankingSelect({ field, place, points, teams, form }) {
    const selectedElsewhere = [form.data.first_team_id, form.data.second_team_id, form.data.third_team_id].filter((id) => id && id !== form.data[field]);
    return <label className="rounded-xl bg-white p-5 shadow-sm"><span className="block text-xl font-bold">{place} <small className="text-base">({points})</small></span><select value={form.data[field]} onChange={(event) => form.setData(field, event.target.value)} className="mt-3 h-11 w-full rounded-lg border-slate-200 bg-slate-50 text-base focus:border-emerald-600 focus:ring-emerald-600"><option value="">Selecione a equipe</option>{teams.map((team) => <option key={team.id} value={team.id} disabled={selectedElsewhere.includes(String(team.id))}>{team.name}</option>)}</select>{form.errors[field] && <small className="mt-2 block text-rose-600">{form.errors[field]}</small>}</label>;
}

function VotingNotice({ votingOpen, teamCount }) {
    if (!votingOpen) return <p className="rounded-xl bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 ring-1 ring-amber-200">A janela de votação ainda não está aberta.</p>;
    if (teamCount < 3) return <p className="rounded-xl bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 ring-1 ring-amber-200">É necessário ter ao menos três pontes aprovadas na auditoria.</p>;
    return <p className="rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 ring-1 ring-emerald-200">Votação aberta. Selecione três equipes diferentes.</p>;
}

function VoteReceipt({ vote }) {
    return <section className="mt-7 overflow-hidden rounded-xl bg-white shadow-sm"><div className="border-b border-slate-200 px-5 py-5 text-center"><p className="text-xl font-bold text-emerald-700">✓ Votação enviada!</p><p className="text-sm text-slate-600">Seu voto único foi registrado.</p></div><div className="grid gap-4 px-5 py-6 text-sm"><p><strong>1º lugar:</strong> {vote.first} <span className="text-slate-500">(15 pontos)</span></p><p><strong>2º lugar:</strong> {vote.second} <span className="text-slate-500">(10 pontos)</span></p><p><strong>3º lugar:</strong> {vote.third} <span className="text-slate-500">(5 pontos)</span></p></div><p className="bg-slate-100 px-5 py-4 text-center text-xs font-semibold text-slate-600">Você não pode votar novamente após enviar sua lista.</p></section>;
}
