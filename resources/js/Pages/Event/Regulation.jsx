import EventLayout from '@/Components/EventLayout';
import { Head, Link } from '@inertiajs/react';

const sections = [
    { number: '01', title: 'Participação e equipes', items: ['Equipes com no mínimo 2 e no máximo 5 integrantes.', 'Todos os participantes devem estar regularmente matriculados no IFSP Campus Caraguatatuba.', 'Cada aluno pode integrar apenas uma equipe.', 'Cada integrante deve confirmar sua participação usando o código fornecido pelo líder.'] },
    { number: '02', title: 'Vagas e categorias', items: ['A competição admite até 15 equipes.', '10 vagas são reservadas para equipes da Engenharia Civil.', '5 vagas são destinadas à ampla concorrência entre os cursos do campus.', 'A disponibilidade é validada automaticamente no momento da inscrição.'] },
    { number: '03', title: 'Construção da ponte', items: ['A ponte deve ter massa máxima de 1.000 g, com tolerância de aferição até 1.010 g.', 'São permitidos palitos de madeira, cola branca ou para madeira e resina epóxi somente nas junções.', 'Não é permitido usar tintas, vernizes ou revestimentos que ocultem a estrutura.', 'Dimensões e condições de montagem devem seguir o edital oficial quando publicado.'] },
    { number: '04', title: 'Avaliação e desempate', items: ['A nota reúne eficiência estrutural, precisão de projeto e avaliação estética.', 'A avaliação estética atribui 15, 10 e 5 pontos ao primeiro, segundo e terceiro lugares.', 'Em caso de empate, prevalecem: precisão, carga real, estética e ordem de entrega.', 'A organização realiza auditoria física antes dos ensaios.'] },
];

export default function Regulation() {
    return <EventLayout active="event.regulation">
        <Head title="Regulamento — Concurso de Pontes de Palito" />
        <main className="institutional-page">
            <PageHero eyebrow="Competição 2026" title="Regulamento" description="Conheça os critérios essenciais para formar sua equipe, construir o protótipo e participar da avaliação." />
            <section className="document-intro"><p>Este resumo facilita a consulta às principais regras da competição. Em caso de divergência, o edital oficial publicado pela organização terá prioridade.</p><Link className="institutional-button" href={route('registration.create')}>Fazer inscrição</Link></section>
            <section className="regulation-grid">
                {sections.map((section) => <article className="rule-section" key={section.number}><span>{section.number}</span><h2>{section.title}</h2><ul>{section.items.map((item) => <li key={item}>{item}</li>)}</ul></article>)}
            </section>
            <aside className="notice-box"><strong>Atenção</strong><p>A inscrição implica concordância com as regras. A comissão organizadora poderá desclassificar protótipos fora dos limites de materiais, peso ou segurança.</p></aside>
        </main>
    </EventLayout>;
}

function PageHero({ eyebrow, title, description }) {
    return <header className="page-hero"><span className="eyebrow">{eyebrow}</span><h1>{title}</h1><p>{description}</p></header>;
}
