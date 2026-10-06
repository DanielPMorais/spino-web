import EventLayout from '@/Components/EventLayout';
import { Head, Link } from '@inertiajs/react';

export default function Schedule({ registrationStartsAt, registrationEndsAt }) {
    const timeline = [
        { phase: 'Inscrições', date: formatRange(registrationStartsAt, registrationEndsAt), text: 'Criação das equipes e confirmação individual dos integrantes.', status: registrationStartsAt || registrationEndsAt ? 'Data configurada' : 'Calendário em definição' },
        { phase: 'Entrega de pontes e pôsteres', date: '19 de outubro de 2026', text: 'Recebimento no LIEC, sala B102, seguido de pesagem e verificação do regulamento.', status: 'LIEC · sala B102' },
        { phase: 'Exposição', date: '20 de outubro de 2026, às 14h', text: 'Exposição pública das pontes e pôsteres para a avaliação de estética.', status: 'Local a definir' },
        { phase: 'Teste de carga', date: '21 de outubro de 2026, às 18h30', text: 'Ruptura das pontes e registro oficial da carga suportada.', status: 'Local a definir' },
        { phase: 'Premiação', date: 'Após o teste de carga', text: 'Divulgação da classificação e premiação das três equipes melhores colocadas.', status: 'Conforme comissão organizadora' },
    ];
    return <EventLayout active="event.schedule">
        <Head title="Cronograma — Concurso de Pontes de Palito" />
        <main className="institutional-page">
            <header className="page-hero"><span className="eyebrow">Planeje sua participação</span><h1>Cronograma</h1><p>Acompanhe as etapas da competição, da formação das equipes até a cerimônia de premiação.</p></header>
            <section className="schedule-summary"><div><strong>5 etapas</strong><span>da inscrição à premiação</span></div><div><strong>Campus Caraguatatuba</strong><span>local do evento</span></div><Link className="institutional-button" href={route('registration.create')}>Inscrever equipe</Link></section>
            <ol className="event-timeline">
                {timeline.map((item, index) => <li key={item.phase}><div className="timeline-marker">{String(index + 1).padStart(2, '0')}</div><article><div className="timeline-meta"><span>{item.status}</span><time>{item.date}</time></div><h2>{item.phase}</h2><p>{item.text}</p></article></li>)}
            </ol>
            <aside className="notice-box"><strong>Calendário oficial</strong><p>As datas serão publicadas nesta página pela comissão organizadora. Antes de preparar a entrega, confira aqui a versão mais recente.</p></aside>
        </main>
    </EventLayout>;
}

function formatRange(start, end) {
    if (!start && !end) return 'Período a confirmar';
    const format = (value) => value ? new Intl.DateTimeFormat('pt-BR', { dateStyle: 'long', timeStyle: 'short' }).format(new Date(value)) : null;
    if (start && end) return `${format(start)} a ${format(end)}`;
    return start ? `A partir de ${format(start)}` : `Até ${format(end)}`;
}
