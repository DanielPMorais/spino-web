import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';

const statusLabel = { pending: 'Aguardando ensaio', running: 'Ensaio em andamento', completed: 'Resultado concluído' };

export default function ManualRuptureResultsIndex({ teams }) {
    const { flash } = usePage().props;
    const [query, setQuery] = useState('');
    const [selected, setSelected] = useState(null);
    const form = useForm({ manual_rupture_load_grams: '', manual_rupture_notes: '' });
    const visible = useMemo(() => teams.filter((team) => `${team.name} ${team.code} ${team.leader}`.toLowerCase().includes(query.toLowerCase())), [teams, query]);

    const open = (team) => {
        setSelected(team);
        form.setData({
            manual_rupture_load_grams: team.manualLoadGrams ?? '',
            manual_rupture_notes: team.manualNotes ?? '',
        });
        form.clearErrors();
    };

    const submit = (event) => {
        event.preventDefault();
        form.post(route('admin.manual-rupture-results.store', selected.id), {
            preserveScroll: true,
            onSuccess: () => { setSelected(null); form.reset(); },
        });
    };

    return <AdminLayout title="Lançamento de ruptura" description="Registro manual de contingência para o peso de ruptura de cada ponte. A leitura do sensor continua sendo a fonte prioritária do placar.">
        <Head title="Lançamento de ruptura" />
        {flash?.success && <p className="mb-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{flash.success}</p>}
        <section className="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div><h2 className="text-xl font-bold">Resultados dos ensaios</h2><p className="mt-1 text-sm text-slate-500">Use este lançamento para manter um registro independente durante o ensaio.</p></div>
                <input value={query} onChange={(event) => setQuery(event.target.value)} placeholder="⌕ Buscar equipe" className="h-10 rounded-lg border-slate-200 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600 sm:w-64" />
            </div>
            <div className="admin-table-wrap mt-6 overflow-x-auto">
                <table className="admin-responsive-table w-full min-w-[850px] text-left text-sm">
                    <thead className="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500"><tr><th className="rounded-l-md px-4 py-3">Equipe</th><th className="px-4 py-3">Carga declarada</th><th className="px-4 py-3">Sensor</th><th className="px-4 py-3">Manual</th><th className="px-4 py-3">Fonte do placar</th><th className="rounded-r-md px-4 py-3 text-right">Ação</th></tr></thead>
                    <tbody className="divide-y divide-slate-200">{visible.map((team) => <tr key={team.id} className="hover:bg-slate-50/70">
                        <td data-label="Equipe" className="px-4 py-4"><strong className="block">{team.name}</strong><small className="text-slate-500">{team.code} · {team.leader}<br />{statusLabel[team.testStatus] ?? team.testStatus}</small></td>
                        <td data-label="Carga declarada" className="px-4 py-4">{formatWeight(team.declaredLoadGrams)}</td>
                        <td data-label="Sensor" className="px-4 py-4">{formatWeight(team.sensorLoadGrams)}</td>
                        <td data-label="Manual" className="px-4 py-4">{formatWeight(team.manualLoadGrams)}</td>
                        <td data-label="Fonte do placar" className="px-4 py-4"><SourceBadge team={team} /></td>
                        <td data-label="Ação" className="px-4 py-4 text-right"><button type="button" onClick={() => open(team)} className="font-semibold text-sky-700 hover:text-sky-900">{team.manualLoadGrams ? 'Editar lançamento' : 'Lançar peso'}</button></td>
                    </tr>)}</tbody>
                </table>
                {!visible.length && <p className="py-12 text-center text-sm text-slate-500">Nenhuma ponte aprovada encontrada.</p>}
            </div>
        </section>
        {selected && <div className="fixed inset-0 z-50 grid items-end bg-slate-950/45 p-3 sm:place-items-center sm:p-4">
            <form onSubmit={submit} className="max-h-[calc(100dvh-1.5rem)] w-full max-w-lg overflow-y-auto rounded-2xl bg-white p-5 shadow-2xl sm:max-h-[calc(100dvh-2rem)] sm:p-6">
                <div className="flex items-start justify-between gap-4"><div><p className="text-xs font-bold uppercase tracking-[.14em] text-sky-700">Plano B de contingência</p><h2 className="mt-1 text-xl font-bold">{selected.name}</h2><p className="mt-1 text-sm text-slate-500">Este dado fica registrado separadamente. Se houver leitura do sensor, ela prevalece no placar.</p></div><button type="button" onClick={() => setSelected(null)} aria-label="Fechar" className="text-2xl leading-none text-slate-400 hover:text-slate-700">×</button></div>
                <label className="mt-6 block text-sm font-semibold">Peso de ruptura (g)<input type="number" min="1" max="1000000" autoFocus value={form.data.manual_rupture_load_grams} onChange={(event) => form.setData('manual_rupture_load_grams', event.target.value)} className="mt-2 block w-full rounded-lg border-slate-200 shadow-sm focus:border-sky-600 focus:ring-sky-600" /></label>
                {form.errors.manual_rupture_load_grams && <small className="mt-1 block text-rose-600">{form.errors.manual_rupture_load_grams}</small>}
                <label className="mt-5 block text-sm font-semibold">Observação <span className="font-normal text-slate-400">(opcional)</span><textarea rows="4" value={form.data.manual_rupture_notes} onChange={(event) => form.setData('manual_rupture_notes', event.target.value)} placeholder="Ex.: leitura anotada pela comissão após interrupção do módulo." className="mt-2 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-sky-600 focus:ring-sky-600" /></label>
                {form.errors.manual_rupture_notes && <small className="mt-1 block text-rose-600">{form.errors.manual_rupture_notes}</small>}
                <div className="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"><button type="button" onClick={() => setSelected(null)} className="min-h-11 rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Cancelar</button><button disabled={form.processing} className="min-h-11 rounded-lg bg-[#172938] px-4 py-2 text-sm font-bold text-white hover:bg-[#223b50] disabled:opacity-50">{form.processing ? 'Salvando…' : 'Salvar lançamento'}</button></div>
            </form>
        </div>}
    </AdminLayout>;
}

function formatWeight(value) { return value === null || value === undefined ? '—' : `${new Intl.NumberFormat('pt-BR').format(value / 1000)} kg`; }
function SourceBadge({ team }) {
    if (team.sensorLoadGrams !== null && team.sensorLoadGrams !== undefined) return <span className="inline-flex rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700 ring-1 ring-sky-200">Sensor</span>;
    if (team.manualLoadGrams !== null && team.manualLoadGrams !== undefined) return <span className="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">Manual</span>;
    return <span className="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200">Sem resultado</span>;
}
