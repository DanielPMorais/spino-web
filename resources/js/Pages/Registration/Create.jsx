import { Head, useForm, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

const emptyStudent = { enrollment: '', name: '', course: '' };
const competitionPhotos = [
    {
        src: '/images/competition-2023/exposicao-pontes.jpg',
        alt: 'Pontes de palito expostas durante a competição de 2023 no Campus Caraguatatuba',
        caption: 'Exposição das pontes — edição 2023',
    },
    {
        src: '/images/competition-2023/teste-carga.jpg',
        alt: 'Estudantes realizando o teste de carga de uma ponte de palitos em 2023',
        caption: 'Teste de carga das estruturas',
    },
    {
        src: '/images/competition-2023/participantes.jpg',
        alt: 'Participantes da Competição de Pontes de Palito de Churrasco de 2023',
        caption: 'Participantes e público da competição',
    },
];

export default function Create({ availability, registrationEndsAt }) {
    const { flash } = usePage().props;
    const [role, setRole] = useState(null);
    const [successDismissed, setSuccessDismissed] = useState(false);
    const [student, setStudent] = useState(emptyStudent);
    const [lookupError, setLookupError] = useState('');
    const [lookingUp, setLookingUp] = useState(false);
    const createForm = useForm({ team_name: '', category: '', email: '', enrollment: '' });
    const joinForm = useForm({ code: '', course: '', email: '', enrollment: '' });
    const form = role === 'leader' ? createForm : joinForm;
    const countdown = useCountdown(registrationEndsAt);

    const chooseRole = (nextRole) => {
        setRole(nextRole);
        setStudent(emptyStudent);
        setLookupError('');
        createForm.clearErrors();
        joinForm.clearErrors();
    };

    const goBack = () => chooseRole(null);
    const startAnotherRegistration = () => {
        setSuccessDismissed(true);
        chooseRole(null);
        createForm.reset();
        joinForm.reset();
    };

    const lookupStudent = async () => {
        const enrollment = form.data.enrollment.trim();
        setStudent(emptyStudent);
        setLookupError('');
        if (!enrollment) return;

        setLookingUp(true);
        try {
            const response = await window.axios.get(`/inscricao/alunos/${encodeURIComponent(enrollment)}`);
            setStudent(response.data);
            if (role === 'member') joinForm.setData('course', response.data.course);
        } catch (error) {
            setLookupError(error.response?.data?.message ?? 'Não foi possível consultar a matrícula.');
        } finally {
            setLookingUp(false);
        }
    };

    const submit = (event) => {
        event.preventDefault();
        if (!student.enrollment) {
            setLookupError('Consulte uma matrícula válida antes de continuar.');
            return;
        }
        form.post(role === 'leader' ? route('registration.store') : route('registration.join'), {
            onSuccess: () => {
                setRole(null);
                setStudent(emptyStudent);
                setLookupError('');
                setSuccessDismissed(false);
            },
        });
    };

    const hasSuccess = Boolean(flash?.success || flash?.teamCode);

    return (
        <>
            <Head title="Inscrição — Concurso de Pontes de Palito" />
            <div className="registration-page">
                <EventHeader />

                <main className="registration-stage">
                    {hasSuccess && !successDismissed ? (
                        <RegistrationSuccess flash={flash} onStartAnother={startAnotherRegistration} />
                    ) : !role ? (
                        <RoleSelection
                            availability={availability}
                            countdown={countdown}
                            onChoose={chooseRole}
                        />
                    ) : (
                        <RegistrationForm
                            role={role}
                            form={form}
                            student={student}
                            lookupError={lookupError}
                            lookingUp={lookingUp}
                            availability={availability}
                            onBack={goBack}
                            onLookup={lookupStudent}
                            onStudentChange={() => setStudent(emptyStudent)}
                            onSubmit={submit}
                        />
                    )}
                </main>

                <footer className="event-footer">© 2026 SPINOSSAURO</footer>
            </div>
        </>
    );
}

function RoleSelection({ availability, countdown, onChoose }) {
    return (
        <div className="role-screen">
            <section className="institutional-hero">
                <div className="hero-copy">
                    <span className="eyebrow">Competição de Pontes de Palito 2026</span>
                    <h1>Desafie a engenharia.<br />Construa para resistir.</h1>
                    <p>Reúna sua equipe, transforme conhecimento em estrutura e coloque seu projeto à prova em uma competição organizada pelo curso de Construção Civil.</p>
                    <div className="registration-status">
                        <span className={availability.isOpen ? 'status-dot is-open' : 'status-dot'} />
                        {availability.isOpen ? <>Inscrições abertas · encerram em <strong>{countdown}</strong></> : 'Inscrições encerradas'}
                    </div>
                </div>
                <CompetitionCarousel />
            </section>

            <section className="event-facts" aria-label="Informações principais da competição">
                <Fact icon="users" value="2 a 5 integrantes" label="por equipe" />
                <Fact icon="trophy" value="15 equipes" label={`${availability.totalRemaining} vagas disponíveis`} />
                <Fact icon="weight" value="Até 1 kg" label="massa total da ponte" />
                <Fact icon="graduation" value="Campus Caraguatatuba" label="alunos matriculados" />
            </section>

            <section className="role-card">
                <div className="section-heading">
                    <span className="eyebrow">Inscrição</span>
                    <h2>Como você vai participar?</h2>
                    <p>Escolha uma opção. Cada integrante confirma sua participação individualmente.</p>
                </div>
                <div className="role-options">
                    <RoleButton role="leader" onClick={() => onChoose('leader')} />
                    <RoleButton role="member" onClick={() => onChoose('member')} />
                </div>
            </section>
        </div>
    );
}

function RegistrationSuccess({ flash, onStartAnother }) {
    const createdTeam = Boolean(flash?.teamCode);

    return (
        <div className="registration-workspace registration-success">
            <aside className="registration-guide">
                <span className="eyebrow">Inscrição concluída</span>
                <h2>{createdTeam ? 'Sua equipe está criada' : 'Participação confirmada'}</h2>
                <p>{createdTeam ? 'Guarde e compartilhe o código abaixo para que os demais integrantes possam entrar na equipe.' : 'Seu ingresso na equipe foi registrado com sucesso.'}</p>
            </aside>
            <section className="form-card">
                <div className="form-progress" aria-label="Etapa 3 de 3">
                    <span className="progress-step is-complete">1</span><i /><span className="progress-step is-complete">2</span><i /><span className="progress-step is-active">3</span>
                </div>
                <div className="success-code">
                    <span>{createdTeam ? 'Equipe criada com sucesso' : 'Você entrou na equipe'}</span>
                    {createdTeam && <strong>{flash.teamCode}</strong>}
                    <p>{flash?.success}</p>
                    <button type="button" onClick={onStartAnother}>Fazer outra inscrição</button>
                </div>
            </section>
        </div>
    );
}

function RoleButton({ role, onClick }) {
    const leader = role === 'leader';
    return (
        <button type="button" className="role-button" onClick={onClick}>
            <span className="role-icon" aria-hidden="true">{leader ? <LeaderBadgeIcon /> : <InviteCodeIcon />}</span>
            <strong>{leader ? 'Sou líder' : 'Recebi um código'}</strong>
            <span>{leader ? 'Quero criar uma equipe e convidar integrantes.' : 'Quero entrar em uma equipe já criada.'}</span>
        </button>
    );
}

function RegistrationForm({ role, form, student, lookupError, lookingUp, availability, onBack, onLookup, onStudentChange, onSubmit }) {
    const leader = role === 'leader';
    const disabled = !availability.isOpen || form.processing;

    return (
        <div className="registration-workspace">
            <aside className="registration-guide">
                <span className="eyebrow">Inscrição</span>
                <h2>{leader ? 'Crie sua equipe' : 'Entre na equipe'}</h2>
                <p>Use seus dados institucionais. As informações do aluno serão confirmadas na base oficial do campus.</p>
                <ul>
                    <li>Equipe de 2 a 5 integrantes</li>
                    <li>Uma equipe por aluno</li>
                    <li>Código de convite com 8 caracteres</li>
                    <li>Confirmação individual obrigatória</li>
                </ul>
                <button type="button" className="text-back-button" onClick={onBack}><BackIcon /> Voltar à escolha</button>
            </aside>
        <section className="form-card">
            <div className="form-progress" aria-label="Etapa 2 de 3">
                <span className="progress-step is-complete">1</span><i /><span className="progress-step is-active">2</span><i /><span className="progress-step">3</span>
            </div>
            <div className="form-heading">
                <div className="form-role-icon">{leader ? <LeaderIcon /> : <MemberIcon />}</div>
                <div>
                    <p>{leader ? 'CRIAR NOVA EQUIPE (LÍDER)' : 'ENTRAR EM GRUPO EXISTENTE (MEMBRO)'}</p>
                    <h1>{leader ? 'COMECE SUA JORNADA' : 'JUNTE-SE AOS SEUS COLEGAS'}</h1>
                </div>
            </div>

            <form onSubmit={onSubmit} className="figma-form">
                {leader ? (
                    <>
                        <CompactField label="Nome da Equipe" error={form.errors.team_name}>
                            <input value={form.data.team_name} onChange={(e) => form.setData('team_name', e.target.value)} disabled={disabled} />
                        </CompactField>
                        <CompactField label="Categoria da Equipe" error={form.errors.category}>
                            <select value={form.data.category} onChange={(e) => form.setData('category', e.target.value)} disabled={disabled}>
                                <option value="">Selecione a categoria da equipe</option>
                                <option value="civil">Engenharia Civil</option>
                                <option value="general">Ampla concorrência</option>
                            </select>
                        </CompactField>
                        <CompactField label="Email acadêmico" error={form.errors.email}>
                            <input type="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} disabled={disabled} />
                        </CompactField>
                        <EnrollmentField form={form} disabled={disabled} error={form.errors.enrollment || lookupError} onLookup={onLookup} onChange={onStudentChange} />
                        {student.enrollment && <StudentConfirmation student={student} />}
                    </>
                ) : (
                    <>
                        <CompactField label="Código Hash do Grupo" error={form.errors.code}>
                            <input value={form.data.code} onChange={(e) => form.setData('code', e.target.value.toUpperCase())} maxLength="8" disabled={disabled} />
                        </CompactField>
                        <CompactField label="Curso" error={form.errors.course}>
                            <select value={form.data.course} onChange={(e) => form.setData('course', e.target.value)} disabled={disabled}>
                                <option value="">Selecione seu curso</option>
                                {student.course && <option value={student.course}>{student.course}</option>}
                                <option value="Engenharia Civil">Engenharia Civil</option>
                                <option value="Tecnologia em Processos Gerenciais">Tecnologia em Processos Gerenciais</option>
                            </select>
                        </CompactField>
                        <CompactField label="Email acadêmico" error={form.errors.email}>
                            <input type="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} disabled={disabled} />
                        </CompactField>
                        <EnrollmentField form={form} disabled={disabled} error={form.errors.enrollment || lookupError} onLookup={onLookup} onChange={onStudentChange} />
                        {student.enrollment && <StudentConfirmation student={student} />}
                    </>
                )}

                {lookingUp && <p className="lookup-status">Consultando matrícula…</p>}
                {form.errors.registration && <p className="form-global-error">{form.errors.registration}</p>}
                <button className="orange-submit" type="submit" disabled={disabled || !student.enrollment}>
                    {form.processing ? 'Processando…' : leader ? 'Confirmar inscrição' : 'Entrar na equipe'}
                </button>
            </form>
        </section>
        </div>
    );
}

function StudentConfirmation({ student }) {
    return (
        <div className="student-confirmation">
            <span className="confirmation-check">✓</span>
            <div><strong>Aluno identificado</strong><span>{student.name} · {student.course}</span></div>
        </div>
    );
}

function EnrollmentField({ form, disabled, error, onLookup, onChange }) {
    return (
        <CompactField label="Matrícula IFSP" error={error}>
            <input
                value={form.data.enrollment}
                onChange={(e) => { form.setData('enrollment', e.target.value); onChange(); }}
                onBlur={onLookup}
                disabled={disabled}
            />
        </CompactField>
    );
}

function CompactField({ label, error, children }) {
    return <label className="compact-field"><span>{label}</span>{children}{error && <small>{error}</small>}</label>;
}

function EventHeader() {
    return (
        <header className="event-header">
            <div className="event-brand">
                <img src="/images/logo-ponte.png" alt="Ponte de macarrão" />
                <span><strong>Concurso de Pontes de Palito</strong><small>Construção Civil</small></span>
            </div>
            <nav className="event-nav" aria-label="Navegação do evento"><a href={route('event.regulation')}>Regulamento</a><a href={route('event.schedule')}>Cronograma</a><a href={route('event.contact')}>Contato</a></nav>
            <div className="institution-logos">
                <img className="casec-logo" src="/images/logo-casec-jr.png" alt="CASEC Jr." />
                <img className="ifsp-logo-image" src="/images/logo-ifsp.png" alt="Instituto Federal de São Paulo — Campus Caraguatatuba" />
            </div>
        </header>
    );
}

function Fact({ icon, value, label }) {
    const icons = { users: '◫', trophy: '◇', weight: '△', graduation: '▱' };
    return <div className="event-fact"><span aria-hidden="true">{icons[icon]}</span><div><strong>{value}</strong><small>{label}</small></div></div>;
}

function CompetitionCarousel() {
    const [activeIndex, setActiveIndex] = useState(0);
    const [paused, setPaused] = useState(false);

    useEffect(() => {
        if (paused) return undefined;

        const timer = window.setInterval(() => {
            setActiveIndex((current) => (current + 1) % competitionPhotos.length);
        }, 5000);

        return () => window.clearInterval(timer);
    }, [paused]);

    const showPrevious = () => setActiveIndex((current) => (current - 1 + competitionPhotos.length) % competitionPhotos.length);
    const showNext = () => setActiveIndex((current) => (current + 1) % competitionPhotos.length);

    return (
        <figure
            className="competition-carousel"
            onMouseEnter={() => setPaused(true)}
            onMouseLeave={() => setPaused(false)}
            onFocus={() => setPaused(true)}
            onBlur={() => setPaused(false)}
            aria-roledescription="carrossel"
            aria-label="Fotos da Competição de Pontes de Palito de 2023"
        >
            <div className="carousel-viewport" aria-live="polite">
                {competitionPhotos.map((photo, index) => (
                    <img
                        key={photo.src}
                        src={photo.src}
                        alt={photo.alt}
                        className={index === activeIndex ? 'is-active' : ''}
                        aria-hidden={index !== activeIndex}
                    />
                ))}
                <div className="carousel-shade" />
                <figcaption>
                    <span>Competição 2023</span>
                    <strong>{competitionPhotos[activeIndex].caption}</strong>
                    <a href="https://www.ifspcaraguatatuba.edu.br/noticias/competicao-de-pontes-de-palito-de-churrasco-2023-agita-o-campus" target="_blank" rel="noreferrer">Fonte: IFSP Caraguatatuba</a>
                </figcaption>
                <button type="button" className="carousel-control is-previous" onClick={showPrevious} aria-label="Foto anterior">‹</button>
                <button type="button" className="carousel-control is-next" onClick={showNext} aria-label="Próxima foto">›</button>
            </div>
            <div className="carousel-dots" aria-label="Selecionar foto">
                {competitionPhotos.map((photo, index) => (
                    <button
                        key={photo.src}
                        type="button"
                        className={index === activeIndex ? 'is-active' : ''}
                        onClick={() => setActiveIndex(index)}
                        aria-label={`Exibir foto ${index + 1}`}
                        aria-current={index === activeIndex ? 'true' : undefined}
                    />
                ))}
            </div>
        </figure>
    );
}
function LeaderIcon({ starred = false }) {
    return <svg viewBox="0 0 70 70" aria-hidden="true"><circle cx="35" cy="20" r="11" fill="currentColor"/><path d="M18 57c1-16 5-26 17-26s16 10 17 26H18z" fill="currentColor"/>{starred && <path d="M51 33l4 8 9 1-7 6 2 9-8-5-8 5 2-9-7-6 9-1z" fill="white" stroke="#1b2d3b" strokeWidth="1.5"/>}</svg>;
}
function MemberIcon() {
    return <svg viewBox="0 0 86 70" aria-hidden="true"><circle cx="28" cy="22" r="9" fill="currentColor"/><circle cx="57" cy="17" r="12" fill="currentColor"/><path d="M13 57c1-14 4-24 15-24s14 10 15 24H13zM39 57c1-18 6-29 18-29s17 11 18 29H39z" fill="currentColor"/></svg>;
}
function LeaderBadgeIcon() {
    return <svg viewBox="0 0 48 48"><path d="M18 25c-6 0-10 4-10 10v2h16"/><circle cx="18" cy="15" r="6"/><path d="M30 16.5c1.6-2.2 4.2-3.5 7-3.5 1.1 0 2.1.2 3 .6V21c0 5.2-3.2 9.3-8 11-4.8-1.7-8-5.8-8-11v-7.4c.9-.4 1.9-.6 3-.6 2.7 0 5.2 1.3 6.8 3.3"/><path d="m28.5 22 2.3 2.3 5-5"/></svg>;
}
function InviteCodeIcon() {
    return <svg viewBox="0 0 48 48"><path d="M18 29a9 9 0 1 1 6.4-2.6L21 30h-4v4h-4v4H7v-6.2l4.6-4.6"/><circle cx="18" cy="20" r="2"/><path d="M31 10h8a3 3 0 0 1 3 3v22a3 3 0 0 1-3 3h-9M34 18h3M34 24h3M30 30h7"/></svg>;
}
function BackIcon() {
    return <svg viewBox="0 0 32 32" aria-hidden="true"><path d="M11 9L6 14l5 5M7 14h9a8 8 0 110 16" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"/></svg>;
}

function useCountdown(endsAt) {
    const fallback = '10 dias 4 horas 20 minutos';
    const [value, setValue] = useState(fallback);

    useEffect(() => {
        if (!endsAt) return;
        const update = () => {
            const remaining = Math.max(0, new Date(endsAt).getTime() - Date.now());
            const days = Math.floor(remaining / 86400000);
            const hours = Math.floor((remaining % 86400000) / 3600000);
            const minutes = Math.floor((remaining % 3600000) / 60000);
            setValue(`${days} dias ${hours} horas ${minutes} minutos`);
        };
        update();
        const timer = window.setInterval(update, 60000);
        return () => window.clearInterval(timer);
    }, [endsAt]);

    return value;
}
