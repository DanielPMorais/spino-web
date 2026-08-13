import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';

const labels = { waiting: 'Pendente', approved: 'Aprovada', rejected: 'Desclassificada' };
const styles = { waiting: 'bg-amber-50 text-amber-700 ring-amber-200', approved: 'bg-emerald-50 text-emerald-700 ring-emerald-200', rejected: 'bg-rose-50 text-rose-700 ring-rose-200' };

export default function BridgeAuditIndex({ teams, metrics }) {
    const { flash } = usePage().props;
    const [query, setQuery] = useState('');
    const [status, setStatus] = useState('all');
    const visible = useMemo(() => teams.filter((team) => (status === 'all' || team.auditStatus === status) && `${team.name} ${team.leader} ${team.code}`.toLowerCase().includes(query.toLowerCase())), [teams, query, status]);

    return <AdminLayout title="Organização de pontes" description="Controle de entrega e auditoria física dos protótipos."><Head title="Organização de pontes" />
        <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Metric label="Pontes entregues" value={metrics.delivered} detail="Equipes habilitadas" color="bg-sky-600" />
            <Metric label="Aprovadas na auditoria" value={metrics.approved} detail="Liberadas para o ensaio" color="bg-emerald-600" />
            <Metric label="Reprovadas na auditoria" value={metrics.rejected} detail="Exigem revisão da comissão" color="bg-rose-600" />
            <Metric label="Em fila para check-in" value={metrics.waiting} detail="Aguardando conferência" color="bg-amber-600" />
        </section>
        <section className="mt-6 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"><div><h2 className="text-xl font-bold">Equipes aguardando auditoria</h2><p className="mt-0.5 text-sm text-slate-500">Selecione uma equipe para registrar a conferência física.</p></div><div className="flex flex-col gap-3 sm:flex-row"><input value={query} onChange={(event) => setQuery(event.target.value)} placeholder="⌕  Buscar equipe" className="h-10 rounded-lg border-slate-200 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600 sm:w-56" /><select value={status} onChange={(event) => setStatus(event.target.value)} className="h-10 rounded-lg border-slate-200 text-sm font-semibold text-slate-700 shadow-sm"><option value="all">Todos os status</option>{Object.entries(labels).map(([value, label]) => <option key={value} value={value}>{label}</option>)}</select></div></div>
            {flash?.success && <p className="mt-5 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{flash.success}</p>}
            <div className="mt-6 overflow-x-auto"><table className="w-full min-w-[760px] text-left text-sm"><thead className="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500"><tr><th className="rounded-l-md px-4 py-3">Código</th><th className="px-4 py-3">Equipe</th><th className="px-4 py-3">Líder</th><th className="px-4 py-3">Curso</th><th className="px-4 py-3">Status</th><th className="rounded-r-md px-4 py-3 text-right">Ação</th></tr></thead><tbody className="divide-y divide-slate-200">{visible.map((team) => <tr key={team.id} className="hover:bg-slate-50/70"><td className="px-4 py-4 font-semibold text-slate-700">{team.code}</td><td className="px-4 py-4 font-semibold">{team.name}</td><td className="px-4 py-4">{team.leader}</td><td className="px-4 py-4">{team.course}</td><td className="px-4 py-4"><span className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ${styles[team.auditStatus]}`}>{labels[team.auditStatus]}</span></td><td className="px-4 py-4 text-right"><Link href={route('admin.bridge-audits.show', team.id)} className="font-semibold text-sky-700 hover:text-sky-900">{team.auditStatus === 'waiting' ? 'Auditar ponte' : 'Ver auditoria'}</Link></td></tr>)}</tbody></table>{!visible.length && <p className="py-12 text-center text-sm text-slate-500">Nenhuma equipe encontrada.</p>}</div>
        </section>
    </AdminLayout>;
}

function Metric({ label, value, detail, color }) { return <article className="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100"><div className="flex items-center justify-between"><p className="text-sm font-medium text-slate-500">{label}</p><span className={`size-3 rounded-full ${color}`} /></div><p className="mt-3 text-3xl font-bold tracking-tight">{value}</p><p className="mt-2 text-sm text-slate-500">{detail}</p></article>; }
