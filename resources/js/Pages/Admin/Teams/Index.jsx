import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';

const emptyMember = () => ({ name: '', email: '', course: '' });

export default function TeamsIndex({ teams }) {
    const { flash } = usePage().props;
    const form = useForm({ name: '', code: '', members: [emptyMember(), emptyMember()] });

    const setMember = (index, field, value) => {
        form.setData('members', form.data.members.map((member, memberIndex) => memberIndex === index ? { ...member, [field]: value } : member));
    };

    const addMember = () => {
        if (form.data.members.length < 5) form.setData('members', [...form.data.members, emptyMember()]);
    };

    const removeMember = (index) => {
        if (form.data.members.length > 2) form.setData('members', form.data.members.filter((_, memberIndex) => memberIndex !== index));
    };

    const submit = (event) => {
        event.preventDefault();
        form.post(route('admin.teams.store'), { preserveScroll: true, onSuccess: () => form.reset('name', 'code', 'members') });
    };

    const destroy = (team) => {
        const confirmation = window.prompt(`Atenção: isso removerá a equipe, integrantes, ensaios e leituras. Digite ${team.code} para confirmar.`);
        if (confirmation === null) return;
        router.delete(route('admin.teams.destroy', team.id), { data: { confirmation_code: confirmation }, preserveScroll: true });
    };

    return <AdminLayout title="Equipes e pontes" description="Cadastre participantes manualmente e remova registros de teste sem acessar o banco."><Head title="Equipes e pontes" />
        {flash?.success && <p className="mb-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{flash.success}</p>}
        <div className="grid gap-6 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
            <section className="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7">
                <p className="text-xs font-bold uppercase tracking-[.14em] text-sky-700">Cadastro manual</p>
                <h2 className="mt-1 text-xl font-bold">Nova equipe</h2>
                <p className="mt-2 text-sm text-slate-500">O primeiro integrante será o líder. Os participantes entram como confirmados e a equipe fica pendente de aprovação.</p>
                <form onSubmit={submit} className="mt-6 grid gap-5">
                    <Field label="Nome da equipe / ponte" error={form.errors.name}><input value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} /></Field>
                    <Field label="Código da equipe (opcional)" error={form.errors.code}><input value={form.data.code} onChange={(event) => form.setData('code', event.target.value.toUpperCase())} placeholder="Gerado automaticamente" /><small className="mt-1 block font-normal text-slate-500">Use apenas letras, números, hífen ou sublinhado.</small></Field>
                    <div className="border-t border-slate-100 pt-5"><div className="flex items-center justify-between"><h3 className="font-bold">Participantes</h3><span className="text-xs text-slate-500">2 a 5 pessoas</span></div>
                        <div className="mt-4 grid gap-4">{form.data.members.map((member, index) => <fieldset key={index} className="rounded-xl border border-slate-200 p-4"><div className="mb-3 flex items-center justify-between"><legend className="text-sm font-bold">{index === 0 ? 'Líder da equipe' : `Integrante ${index + 1}`}</legend>{index >= 2 && <button type="button" onClick={() => removeMember(index)} className="text-xs font-bold text-rose-700 hover:text-rose-900">Remover</button>}</div><div className="grid gap-3"><Field label="Nome" error={form.errors[`members.${index}.name`]}><input value={member.name} onChange={(event) => setMember(index, 'name', event.target.value)} /></Field><Field label="E-mail" error={form.errors[`members.${index}.email`]}><input type="email" value={member.email} onChange={(event) => setMember(index, 'email', event.target.value)} /></Field><Field label="Curso" error={form.errors[`members.${index}.course`]}><input value={member.course} onChange={(event) => setMember(index, 'course', event.target.value)} /></Field></div></fieldset>)}</div>
                        {form.data.members.length < 5 && <button type="button" onClick={addMember} className="mt-4 text-sm font-bold text-sky-700 hover:text-sky-900">+ Adicionar integrante</button>}
                    </div>
                    <button disabled={form.processing} className="rounded-lg bg-[#172938] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#223b50] disabled:opacity-50">{form.processing ? 'Cadastrando…' : 'Cadastrar equipe'}</button>
                </form>
            </section>
            <section className="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7">
                <h2 className="text-xl font-bold">Equipes cadastradas</h2>
                <p className="mt-1 text-sm text-slate-500">{teams.length} registro(s) no banco.</p>
                <div className="mt-6 grid gap-4">{teams.map((team) => <article key={team.id} className="rounded-xl border border-slate-200 p-4"><div className="flex flex-col justify-between gap-3 sm:flex-row sm:items-start"><div><p className="font-bold">{team.name}</p><p className="mt-1 font-mono text-xs text-slate-500">{team.code} · {team.status}</p></div><button type="button" onClick={() => destroy(team)} className="self-start rounded-md px-2 py-1 text-xs font-bold text-rose-700 hover:bg-rose-50">Excluir</button></div><ul className="mt-3 grid gap-1 text-sm text-slate-600">{team.members.map((member) => <li key={member.email}>{member.isLeader ? 'Líder: ' : ''}{member.name} · {member.course}</li>)}</ul></article>)}</div>
                {!teams.length && <p className="py-12 text-center text-sm text-slate-500">Nenhuma equipe cadastrada.</p>}
            </section>
        </div>
    </AdminLayout>;
}

function Field({ label, error, children }) {
    return <label className="text-sm font-semibold">{label}<span className="mt-2 block [&_input]:block [&_input]:w-full [&_input]:rounded-lg [&_input]:border-slate-200 [&_input]:shadow-sm [&_input]:focus:border-sky-600 [&_input]:focus:ring-sky-600">{children}</span>{error && <small className="mt-1 block text-rose-600">{error}</small>}</label>;
}
