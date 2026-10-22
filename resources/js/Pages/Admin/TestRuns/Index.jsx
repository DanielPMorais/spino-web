import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';

const status = {
    armed: { label: 'Armado', className: 'bg-amber-50 text-amber-700 ring-amber-200' },
    running: { label: 'Em andamento', className: 'bg-sky-50 text-sky-700 ring-sky-200' },
    finished: { label: 'Encerrado', className: 'bg-emerald-50 text-emerald-700 ring-emerald-200' },
    cancelled: { label: 'Cancelado', className: 'bg-slate-100 text-slate-600 ring-slate-200' },
};

export default function TestRunsIndex({ teams, devices, runs }) {
    const { flash } = usePage().props;
    const form = useForm({ team_id: '', device_code: devices[0]?.code ?? 'M2-01' });

    const arm = (event) => {
        event.preventDefault();
        form.post(route('admin.test-runs.store'), { preserveScroll: true, onSuccess: () => form.reset('team_id') });
    };

    const action = (run, name) => router.post(route(`admin.test-runs.${name}`, run.id), {}, { preserveScroll: true });

    return <AdminLayout title="Controle de ensaios" description="Vincule a equipe ao M2 antes da ruptura. Só leituras do dispositivo e ensaio armados serão aceitas.">
        <Head title="Controle de ensaios" />
        {flash?.success && <p className="mb-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{flash.success}</p>}
        <div className="grid gap-6 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.4fr)]">
            <section className="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7">
                <p className="text-xs font-bold uppercase tracking-[.14em] text-sky-700">Preparação</p>
                <h2 className="mt-1 text-xl font-bold">Armar novo ensaio</h2>
                <p className="mt-2 text-sm text-slate-500">Confirme a equipe e o identificador físico exibido no Raspberry, por exemplo <strong>M2-01</strong>.</p>
                <form onSubmit={arm} className="mt-6 grid gap-5">
                    <label className="text-sm font-semibold">Equipe
                        <select value={form.data.team_id} onChange={(event) => form.setData('team_id', event.target.value)} className="mt-2 block w-full rounded-lg border-slate-200 shadow-sm focus:border-sky-600 focus:ring-sky-600">
                            <option value="">Selecione uma equipe aprovada</option>
                            {teams.map((team) => <option key={team.id} value={team.id}>{team.name} · {team.code}</option>)}
                        </select>
                        {form.errors.team_id && <small className="mt-1 block text-rose-600">{form.errors.team_id}</small>}
                    </label>
                    <label className="text-sm font-semibold">Dispositivo M2
                        <input list="devices" value={form.data.device_code} onChange={(event) => form.setData('device_code', event.target.value.toUpperCase())} placeholder="M2-01" maxLength="32" className="mt-2 block w-full rounded-lg border-slate-200 shadow-sm focus:border-sky-600 focus:ring-sky-600" />
                        <datalist id="devices">{devices.map((device) => <option key={device.id} value={device.code}>{device.name ?? 'Dispositivo cadastrado'}</option>)}</datalist>
                        <small className="mt-1 block font-normal text-slate-500">Um identificador novo será cadastrado ao armar o primeiro ensaio.</small>
                        {form.errors.device_code && <small className="mt-1 block text-rose-600">{form.errors.device_code}</small>}
                    </label>
                    <button disabled={form.processing} className="rounded-lg bg-[#172938] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#223b50] disabled:opacity-50">{form.processing ? 'Armando…' : 'Armar ensaio'}</button>
                </form>
            </section>
            <section className="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7">
                <div><p className="text-xs font-bold uppercase tracking-[.14em] text-sky-700">Rastreabilidade</p><h2 className="mt-1 text-xl font-bold">Ensaios recentes</h2><p className="mt-1 text-sm text-slate-500">M2 consulta o vínculo armado e envia medições com o ID único do ensaio.</p></div>
                <div className="admin-table-wrap mt-6 overflow-x-auto"><table className="admin-responsive-table w-full min-w-[680px] text-left text-sm">
                    <thead className="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500"><tr><th className="rounded-l-md px-4 py-3">Equipe</th><th className="px-4 py-3">M2</th><th className="px-4 py-3">Estado</th><th className="rounded-r-md px-4 py-3 text-right">Ações</th></tr></thead>
                    <tbody className="divide-y divide-slate-200">{runs.map((run) => <tr key={run.id} className="hover:bg-slate-50/70">
                        <td data-label="Equipe" className="px-4 py-4"><strong className="block">{run.team.name}</strong><small className="font-mono text-xs text-slate-500">{run.team.code} · {run.uuid.slice(0, 8)}</small></td>
                        <td data-label="M2" className="px-4 py-4 font-semibold">{run.deviceCode}</td>
                        <td data-label="Estado" className="px-4 py-4"><StatusBadge value={run.status} /></td>
                        <td data-label="Ações" className="px-4 py-4 text-right">{['armed', 'running'].includes(run.status) && <div className="flex justify-end gap-3">{run.status === 'armed' && <button type="button" onClick={() => action(run, 'start')} className="font-semibold text-sky-700 hover:text-sky-900">Iniciar</button>}<button type="button" onClick={() => action(run, 'finish')} className="font-semibold text-emerald-700 hover:text-emerald-900">Encerrar</button><button type="button" onClick={() => action(run, 'cancel')} className="font-semibold text-rose-700 hover:text-rose-900">Cancelar</button></div>}</td>
                    </tr>)}</tbody>
                </table>{!runs.length && <p className="py-12 text-center text-sm text-slate-500">Nenhum ensaio registrado.</p>}</div>
            </section>
        </div>
    </AdminLayout>;
}

function StatusBadge({ value }) {
    const item = status[value] ?? status.cancelled;
    return <span className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ${item.className}`}>{item.label}</span>;
}
