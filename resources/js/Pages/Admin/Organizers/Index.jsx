import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';

export default function OrganizersIndex({ organizers }) {
    const { flash } = usePage().props;
    const form = useForm({ name: '', email: '', password: '', password_confirmation: '' });

    const submit = (event) => {
        event.preventDefault();
        form.post(route('admin.organizers.store'), { preserveScroll: true, onSuccess: () => form.reset() });
    };

    const destroy = (user) => {
        if (!window.confirm(`Remover o acesso de ${user.name}? Esta pessoa não conseguirá mais entrar no painel.`)) return;
        router.delete(route('admin.organizers.destroy', user.id), { preserveScroll: true });
    };

    return <AdminLayout title="Organizadores" description="Crie e remova acessos operacionais para a comissão. Organizadores não podem criar ou administrar usuários.">
        <Head title="Organizadores" />
        {flash?.success && <p className="mb-6 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{flash.success}</p>}
        <div className="grid gap-6 xl:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)]">
            <section className="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7">
                <p className="text-xs font-bold uppercase tracking-[.14em] text-sky-700">Administração da plataforma</p>
                <h2 className="mt-1 text-xl font-bold">Novo organizador</h2>
                <p className="mt-2 text-sm text-slate-500">A conta terá acesso aos módulos do evento, mas não a esta tela nem à criação de usuários.</p>
                <form onSubmit={submit} className="mt-6 grid gap-5">
                    <Field label="Nome completo" error={form.errors.name}><input value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} autoComplete="name" /></Field>
                    <Field label="E-mail" error={form.errors.email}><input type="email" value={form.data.email} onChange={(event) => form.setData('email', event.target.value)} autoComplete="email" /></Field>
                    <Field label="Senha inicial" error={form.errors.password}><input type="password" value={form.data.password} onChange={(event) => form.setData('password', event.target.value)} autoComplete="new-password" /><small className="mt-1 block font-normal text-slate-500">Mínimo de 12 caracteres.</small></Field>
                    <Field label="Confirmar senha"><input type="password" value={form.data.password_confirmation} onChange={(event) => form.setData('password_confirmation', event.target.value)} autoComplete="new-password" /></Field>
                    <button disabled={form.processing} className="rounded-lg bg-[#172938] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#223b50] disabled:opacity-50">{form.processing ? 'Criando…' : 'Criar organizador'}</button>
                </form>
            </section>
            <section className="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7">
                <h2 className="text-xl font-bold">Acessos existentes</h2>
                <p className="mt-1 text-sm text-slate-500">{organizers.length} organizador(es) cadastrado(s).</p>
                <div className="admin-table-wrap mt-6 overflow-x-auto"><table className="admin-responsive-table w-full min-w-[560px] text-left text-sm">
                    <thead className="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500"><tr><th className="rounded-l-md px-4 py-3">Nome</th><th className="px-4 py-3">E-mail</th><th className="rounded-r-md px-4 py-3 text-right">Ação</th></tr></thead>
                    <tbody className="divide-y divide-slate-200">{organizers.map((user) => <tr key={user.id}><td data-label="Nome" className="px-4 py-4 font-semibold">{user.name}</td><td data-label="E-mail" className="px-4 py-4 text-slate-600">{user.email}</td><td data-label="Ação" className="px-4 py-4 text-right"><button type="button" onClick={() => destroy(user)} className="rounded-md px-2 py-1 text-xs font-bold text-rose-700 hover:bg-rose-50">Excluir</button></td></tr>)}</tbody>
                </table>{!organizers.length && <p className="py-12 text-center text-sm text-slate-500">Nenhum organizador cadastrado.</p>}</div>
            </section>
        </div>
    </AdminLayout>;
}

function Field({ label, error, children }) {
    return <label className="text-sm font-semibold">{label}<span className="mt-2 block [&_input]:block [&_input]:w-full [&_input]:rounded-lg [&_input]:border-slate-200 [&_input]:shadow-sm [&_input]:focus:border-sky-600 [&_input]:focus:ring-sky-600">{children}</span>{error && <small className="mt-1 block text-rose-600">{error}</small>}</label>;
}
