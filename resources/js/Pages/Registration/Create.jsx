import { Head, useForm, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

const competitionPhotos = [
    {
        src: '/images/competition-2023/exposicao-pontes.jpg',
        alt: 'Pontes de palito expostas durante a competição de 2023 no Campus Caraguatatuba',
        caption: 'Exposição das pontes — edição 2023',
        edition: 'Competição 2023',
    },
    {
        src: '/images/competition-2023/teste-carga.jpg',
        alt: 'Estudantes realizando o teste de carga de uma ponte de palitos em 2023',
        caption: 'Teste de carga das estruturas',
        edition: 'Competição 2023',
    },
    {
        src: '/images/competition-2023/participantes.jpg',
        alt: 'Participantes da Competição de Pontes de Palito de Churrasco de 2023',
        caption: 'Participantes da competição — edição 2023',
        edition: 'Competição 2023',
    },
    {
        src: '/images/competition-history/pontes-de-palito-2025-2.jpeg',
        alt: 'Ponte de palito exposta na competição de 2025',
        caption: 'Exposição das pontes — edição 2025',
        edition: 'Competição 2025',
    },
    {
        src: '/images/competition-history/pesagem-2025.jpeg',
        alt: 'Pesagem e teste de carga de uma ponte de palito na competição de 2025',
        caption: 'Teste de carga — edição 2025',
        edition: 'Competição 2025',
    },
    {
        src: '/images/competition-history/pontes-de-palito-2025.jpeg',
        alt: 'Pontes de palito participantes da competição de 2025',
        caption: 'Exposição das pontes — edição 2025',
        edition: 'Competição 2025',
    },
    {
        src: '/images/competition-history/ponte-palito-2025.png',
        alt: 'Montagem com equipes e pontes da competição de palito de 2025',
        caption: 'Pódio — edição 2025',
        edition: 'Competição 2025',
    },
];
const campusCourses = ['Técnico em Edificações', 'Bacharelado em Engenharia Civil'];

export default function Create({ availability, registrationEndsAt, emailVerification }) {
    const { flash } = usePage().props;
    const [role, setRole] = useState(null);
    const [successDismissed, setSuccessDismissed] = useState(false);
    const createForm = useForm({ team_name: '', name: '', course: '', email: '' });
    const joinForm = useForm({ code: '', name: '', course: '', email: '' });
    const form = role === 'leader' ? createForm : joinForm;
    const countdown = useCountdown(registrationEndsAt);

    const chooseRole = (nextRole) => {
        setRole(nextRole);
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

    const submit = (event) => {
        event.preventDefault();
        form.post(role === 'leader' ? route('registration.store') : route('registration.join'), {
            onSuccess: () => {
                setRole(null);
                setSuccessDismissed(false);
            },
        });
    };

    const verification = flash?.verificationRequired
        ? { email: flash.verificationEmail }
        : emailVerification;
    const hasSuccess = Boolean(flash?.success || flash?.teamCode || verification);

    return (
        <>
            <Head title="Inscrição — Concurso de Pontes de Palito" />
            <div className="registration-page">
                <EventHeader />

                <main className="registration-stage">
                    {hasSuccess && !successDismissed ? (
                        <RegistrationSuccess flash={flash} verification={verification} onStartAnother={startAnotherRegistration} />
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
                            availability={availability}
                            onBack={goBack}
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
                <Fact icon="graduation" value="E-mail acadêmico" label="@aluno.ifsp.edu.br" />
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

function RegistrationSuccess({ flash, verification, onStartAnother }) {
    const createdTeam = Boolean(flash?.teamCode);

    if (verification) {
        return <EmailVerification email={verification.email} teamCode={flash?.teamCode} onStartAnother={onStartAnother} />;
    }

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

function EmailVerification({ email, teamCode, onStartAnother }) {
    const form = useForm({ code: '' });
    const resend = useForm({});

    const submit = (event) => {
        event.preventDefault();
        form.post(route('registration.verify-email'));
    };

    return (
        <div className="registration-workspace registration-success">
            <aside className="registration-guide">
                <span className="eyebrow">Confirmação necessária</span>
                <h2>Verifique seu e-mail</h2>
                <p>Enviamos um código de seis dígitos para seu e-mail acadêmico. A participação seguirá para análise após a confirmação.</p>
                {teamCode && <p className="verification-team-code">Código da equipe: <strong>{teamCode}</strong></p>}
            </aside>
            <section className="form-card">
                <div className="form-progress" aria-label="Etapa 3 de 3">
                    <span className="progress-step is-complete">1</span><i /><span className="progress-step is-complete">2</span><i /><span className="progress-step is-active">3</span>
                </div>
                <form onSubmit={submit} className="figma-form verification-form">
                    <div className="verification-copy">
                        <span>Código enviado para</span>
                        <strong>{email}</strong>
                    </div>
                    <CompactField label="Código de confirmação" error={form.errors.code}>
                        <input
                            value={form.data.code}
                            onChange={(event) => form.setData('code', event.target.value.replace(/\D/g, '').slice(0, 6))}
                            inputMode="numeric"
                            autoComplete="one-time-code"
                            maxLength="6"
                            placeholder="000000"
                            disabled={form.processing}
                        />
                    </CompactField>
                    <button className="orange-submit" type="submit" disabled={form.processing}>
                        {form.processing ? 'Confirmando…' : 'Confirmar e-mail'}
                    </button>
                    <button
                        type="button"
                        className="resend-code-button"
                        disabled={resend.processing}
                        onClick={() => resend.post(route('registration.resend-email'))}
                    >
                        {resend.processing ? 'Enviando…' : 'Reenviar código'}
                    </button>
                    <button type="button" className="cancel-verification-button" onClick={onStartAnother}>Voltar ao início</button>
                </form>
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

function RegistrationForm({ role, form, availability, onBack, onSubmit }) {
    const leader = role === 'leader';
    const disabled = !availability.isOpen || form.processing;

    return (
        <div className="registration-workspace">
            <aside className="registration-guide">
                <span className="eyebrow">Inscrição</span>
                <h2>{leader ? 'Crie sua equipe' : 'Entre na equipe'}</h2>
                <p>Use seu e-mail acadêmico do IFSP. A aprovação final da participação será feita pela comissão organizadora.</p>
                <ul>
                    <li>Equipe de 2 a 5 integrantes</li>
                    <li>Um e-mail acadêmico por equipe</li>
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
                        <ParticipantFields form={form} disabled={disabled} />
                    </>
                ) : (
                    <>
                        <CompactField label="Código Hash do Grupo" error={form.errors.code}>
                            <input value={form.data.code} onChange={(e) => form.setData('code', e.target.value.toUpperCase())} maxLength="8" disabled={disabled} />
                        </CompactField>
                        <ParticipantFields form={form} disabled={disabled} />
                    </>
                )}

                {form.errors.registration && <p className="form-global-error">{form.errors.registration}</p>}
                <button className="orange-submit" type="submit" disabled={disabled}>
                    {form.processing ? 'Processando…' : leader ? 'Confirmar inscrição' : 'Entrar na equipe'}
                </button>
            </form>
        </section>
        </div>
    );
}

function ParticipantFields({ form, disabled }) {
    const localPart = form.data.email.replace(/@aluno\.ifsp\.edu\.br$/i, '');
    return <><CompactField label="Nome completo" error={form.errors.name}><input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} disabled={disabled} /></CompactField><CompactField label="Curso" error={form.errors.course}><select value={form.data.course} onChange={(e) => form.setData('course', e.target.value)} disabled={disabled}><option value="">Selecione seu curso</option>{campusCourses.map((course) => <option key={course} value={course}>{course}</option>)}</select></CompactField><CompactField label="E-mail acadêmico" error={form.errors.email}><div className="academic-email"><input value={localPart} onChange={(e) => form.setData('email', `${e.target.value.replace(/@.*/, '')}@aluno.ifsp.edu.br`)} disabled={disabled} autoCapitalize="none" autoComplete="username" /><span>@aluno.ifsp.edu.br</span></div></CompactField></>;
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
                <div className="institution-logo-group"><span>ORGANIZAÇÃO</span><img className="ifsp-logo-image" src="/images/logo-ifsp.png" alt="Técnicas do laboratório do IFSP Campus Caraguatatuba — organização" /></div>
                <div className="institution-logo-group"><span>APOIO</span><img className="casec-logo" src="/images/logo-casec-jr.png" alt="CASEC Jr. — apoio" /></div>
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
            aria-label="Fotos históricas das competições de pontes"
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
                    <span>{competitionPhotos[activeIndex].edition}</span>
                    <strong>{competitionPhotos[activeIndex].caption}</strong>
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
