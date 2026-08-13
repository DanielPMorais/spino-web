import { Link } from '@inertiajs/react';

const navigation = [
    { label: 'Inscrição', route: 'registration.create' },
    { label: 'Regulamento', route: 'event.regulation' },
    { label: 'Cronograma', route: 'event.schedule' },
    { label: 'Contato', route: 'event.contact' },
];

export default function EventLayout({ active, children }) {
    return (
        <div className="event-site">
            <header className="event-header">
                <Link className="event-brand" href={route('registration.create')}>
                    <img src="/images/logo-ponte.png" alt="" />
                    <span><strong>Concurso de Pontes de Palito</strong><small>Construção Civil</small></span>
                </Link>
                <nav className="event-nav" aria-label="Navegação principal">
                    {navigation.map((item) => <Link key={item.route} className={active === item.route ? 'is-active' : ''} href={route(item.route)}>{item.label}</Link>)}
                </nav>
                <div className="institution-logos">
                    <img className="casec-logo" src="/images/logo-casec-jr.png" alt="CASEC Jr." />
                    <img className="ifsp-logo-image" src="/images/logo-ifsp.png" alt="IFSP Campus Caraguatatuba" />
                </div>
            </header>
            {children}
            <footer className="institutional-footer">
                <div><strong>Concurso de Pontes de Palito</strong><span>Curso de Construção Civil · IFSP Campus Caraguatatuba</span></div>
                <nav aria-label="Links do rodapé">{navigation.slice(1).map((item) => <Link key={item.route} href={route(item.route)}>{item.label}</Link>)}</nav>
            </footer>
        </div>
    );
}
