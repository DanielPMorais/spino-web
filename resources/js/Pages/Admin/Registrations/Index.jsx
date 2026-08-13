import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';

const statusLabels = { forming: 'Em formação', pending: 'Pendente', approved: 'Aprovada', rejected: 'Rejeitada' };
const statusStyles = { forming: 'bg-slate-100 text-slate-700 ring-slate-200', pending: 'bg-amber-50 text-amber-700 ring-amber-200', approved: 'bg-emerald-50 text-emerald-700 ring-emerald-200', rejected: 'bg-rose-50 text-rose-700 ring-rose-200' };

export default function RegistrationIndex({ teams, metrics }) {
    const { flash } = usePage().props;
    const [query, setQuery] = useState('');
    const [status, setStatus] = useState('all');
    const [updating, setUpdating] = useState(null);
    const filteredTeams = useMemo(() => teams.filter((team) => {
        const text = `${team.name} ${team.leader} ${team.code}`.toLowerCase();
        return (status === 'all' || team.status === status) && text.includes(query.toLowerCase());
    }), [teams, query, status]);

    const updateStatus = (team, nextStatus) => {
        setUpdating(team.id);
        router.patch(route('admin.registrations.status', team.id), { status: nextStatus }, { preserveScroll: true, onFinish: () => setUpdating(null) });
    };

    const actions = <button type="button" onClick={() => window.print()} className="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">↓ Exportar lista</button>;

    return <AdminLayout title="Gestão de inscrições" description="Acompanhe, valide e administre as equipes inscritas." actions={actions}><Head title="Administração — Inscrições" />
        <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Resumo das inscrições">
            <MetricCard label="Equipes inscritas" value={`${metrics.total} / ${metrics.totalLimit}`} detail="Limite máximo de equipes" accent="bg-sky-600" />
            <MetricCard label="Cota Eng. Civil" value={`${metrics.civil} / ${metrics.civilLimit}`} detail="Vagas reservadas ocupadas" accent="bg-emerald-600" />
            <MetricCard label="Aguardando análise" value={metrics.pending} detail="Equipes que exigem validação" accent="bg-amber-600" />
            <MetricCard label="Inscrições aprovadas" value={metrics.approved} detail={`${metrics.total ? Math.round((metrics.approved / metrics.total) * 100) : 0}% das equipes ativas`} accent="bg-emerald-600" />
        </section>

        <section className="mt-6 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"><div><h2 className="text-xl font-bold">Equipes inscritas</h2><p className="mt-0.5 text-sm text-slate-500">{teams.length} equipes registradas no evento</p></div><div className="flex flex-col gap-3 sm:flex-row"><label className="relative"><span className="sr-only">Buscar equipe</span><input value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Buscar equipe" className="h-10 w-full rounded-lg border-slate-200 pl-9 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600 sm:w-56" /><span className="pointer-events-none absolute left-3 top-2.5 text-slate-400">⌕</span></label><select value={status} onChange={(event) => setStatus(event.target.value)} className="h-10 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm"><option value="all">Todos os status</option>{Object.entries(statusLabels).map(([value, label]) => <option key={value} value={value}>{label}</option>)}</select></div></div>
            {flash?.success && <p className="mt-5 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{flash.success}</p>}
            <div className="mt-6 overflow-x-auto"><table className="w-full min-w-[800px] text-left text-sm"><thead className="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500"><tr><th className="rounded-l-md px-4 py-3">Código</th><th className="px-4 py-3">Equipe</th><th className="px-4 py-3">Líder</th><th className="px-4 py-3">Curso</th><th className="px-4 py-3">Status</th><th className="rounded-r-md px-4 py-3 text-right">Ações</th></tr></thead><tbody className="divide-y divide-slate-200">{filteredTeams.map((team) => <TeamRow key={team.id} team={team} updating={updating === team.id} onUpdate={updateStatus} />)}</tbody></table>{!filteredTeams.length && <p className="py-14 text-center text-sm text-slate-500">Nenhuma equipe encontrada com os filtros atuais.</p>}</div>
            <p className="mt-5 text-sm text-slate-500">Exibindo {filteredTeams.length} de {teams.length} equipes</p>
        </section>
    </AdminLayout>;
}

function MetricCard({ label, value, detail, accent }) { return <article className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100"><div className="flex items-center justify-between"><p className="text-sm font-medium text-slate-500">{label}</p><span className={`size-3 rounded-full ${accent}`} /></div><p className="mt-3 text-3xl font-bold tracking-tight">{value}</p><p className="mt-2 text-sm text-slate-500">{detail}</p></article>; }
function TeamRow({ team, updating, onUpdate }) { return <tr className="hover:bg-slate-50/70"><td className="px-4 py-4 font-semibold text-slate-700">{team.code}</td><td className="px-4 py-4 font-semibold">{team.name}</td><td className="px-4 py-4">{team.leader}</td><td className="px-4 py-4">{team.course}</td><td className="px-4 py-4"><span className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ${statusStyles[team.status]}`}>{statusLabels[team.status]}</span></td><td className="px-4 py-4"><div className="flex justify-end gap-2">{team.status !== 'approved' && <button disabled={updating} onClick={() => onUpdate(team, 'approved')} className="rounded-md px-2 py-1 text-xs font-bold text-emerald-700 hover:bg-emerald-50 disabled:opacity-50">Aprovar</button>}{team.status !== 'rejected' && <button disabled={updating} onClick={() => onUpdate(team, 'rejected')} className="rounded-md px-2 py-1 text-xs font-bold text-rose-700 hover:bg-rose-50 disabled:opacity-50">Rejeitar</button>}</div></td></tr>; }
