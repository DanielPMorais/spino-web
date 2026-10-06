import EventLayout from '@/Components/EventLayout';
import { Head, Link } from '@inertiajs/react';

const sections = [
    { number: '01', title: 'Participação e equipes', items: ['Equipes com no mínimo 2 e no máximo 5 integrantes; participação limitada a 15 equipes.', 'Todos os integrantes devem estar regularmente matriculados no IFSP Campus Caraguatatuba e ter IRA igual ou superior a 6,0.', 'Cada grupo participa com uma única ponte e cada integrante confirma a participação com o código fornecido pelo líder.'] },
    { number: '02', title: 'Construção e materiais', items: ['Ponte treliçada, indivisível, para vão livre de 80 cm; comprimento máximo de 100 cm e peso total máximo de 1.000 g.', 'Use exclusivamente palitos de churrasco, cola para madeira e massa epóxi; não use pintura, revestimento ou outra cola.', 'A ponte deve ter barra de aço de 8 mm na região central, com acesso inferior livre para o mosquetão; os apoios usam tubos de PVC de 20 mm.'] },
    { number: '03', title: 'Entrega e pôster', items: ['A equipe deve entregar a ponte e um pôster A3 em 19 de outubro, no LIEC, sala B102.', 'O pôster deve trazer nome do grupo, integrantes com cursos e períodos, carga de projeto, imagem do projeto e peso próprio.', 'O IFSP fornece 600 palitos de 25 cm. Em contrapartida, cada equipe doa 5 kg de arroz, 2 kg de feijão, 1 kg de macarrão e 1 litro de óleo.'] },
    { number: '04', title: 'Avaliação e desempate', items: ['A pontuação final soma eficiência estrutural, precisão de projeto e estética.', 'A eficiência é a razão entre carga de ruptura e peso próprio. A precisão considera a proximidade entre a carga prevista e a carga de ruptura.', 'A estética concede 15, 10 e 5 pontos às três primeiras pontes de cada avaliador. Em caso de empate: precisão, carga de ruptura, estética e ordem de entrega.'] },
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
