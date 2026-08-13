import EventLayout from '@/Components/EventLayout';
import { Head, Link } from '@inertiajs/react';

export default function Contact({ contactEmail }) {
    const hasContactEmail = Boolean(contactEmail);
    return <EventLayout active="event.contact">
        <Head title="Contato — Concurso de Pontes de Palito" />
        <main className="institutional-page">
            <header className="page-hero"><span className="eyebrow">Fale com a organização</span><h1>Contato</h1><p>Encontre orientação para dúvidas sobre inscrição, regras, documentação da equipe e participação no evento.</p></header>
            <section className="contact-layout">
                <div className="contact-primary"><span className="contact-icon" aria-hidden="true">@</span><span className="eyebrow">Atendimento institucional</span><h2>Comissão organizadora</h2><p>{hasContactEmail ? 'Envie sua dúvida para a comissão organizadora. Responderemos assim que possível.' : 'Para dúvidas antes da divulgação do canal digital, procure a coordenação do curso de Construção Civil no campus.'}</p>{hasContactEmail ? <a className="contact-link" href={`mailto:${contactEmail}`}>{contactEmail}</a> : <span className="unavailable-contact">Atendimento presencial no campus</span>}</div>
                <div className="contact-options">
                    <article><span>01</span><h3>Dúvidas de inscrição</h3><p>Problemas com matrícula, código da equipe, vagas ou confirmação de integrantes.</p></article>
                    <article><span>02</span><h3>Regulamento técnico</h3><p>Materiais permitidos, peso, auditoria e critérios de avaliação.</p></article>
                    <article><span>03</span><h3>Atendimento presencial</h3><p>IFSP Campus Caraguatatuba · Coordenação do curso de Construção Civil.</p></article>
                </div>
            </section>
            <section className="quick-help"><div><span className="eyebrow">Antes de entrar em contato</span><h2>Talvez sua resposta já esteja aqui</h2></div><nav><Link href={route('event.regulation')}>Consultar regulamento <span>→</span></Link><Link href={route('event.schedule')}>Consultar cronograma <span>→</span></Link><Link href={route('registration.create')}>Ir para inscrição <span>→</span></Link></nav></section>
        </main>
    </EventLayout>;
}
