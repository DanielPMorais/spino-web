import { Head, Link } from '@inertiajs/react';

export default function Welcome({ auth }) {
    return (
        <>
            <Head title="Bem-vindo ao Spino" />
            <div className="bg-gray-900 text-white min-h-screen flex flex-col items-center justify-center selection:bg-teal-500 selection:text-white">
                <div className="text-center">
                    <h1 className="text-5xl font-bold tracking-tight text-teal-400 sm:text-7xl">
                        Spino
                    </h1>
                    <p className="mt-6 text-lg leading-8 text-gray-300">
                        Cérebro Central de Sensoriamento e Gestão de Eventos
                    </p>
                    <div className="mt-10 flex items-center justify-center gap-x-6">
                        {auth.user ? (
                            <Link
                                href={route('dashboard')}
                                className="rounded-md bg-teal-500 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-400"
                            >
                                Ir para o Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={route('login')}
                                    className="rounded-md bg-white/10 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-white/20"
                                >
                                    Log in
                                </Link>
                                <Link
                                    href={route('register')}
                                    className="rounded-md bg-teal-500 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-400"
                                >
                                    Registrar
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
