# Spino Central

Aplicação web do Módulo 3 do Projeto Spino: cadastro de equipes, inscrições, regras do concurso e, nas próximas etapas, telemetria e placar ao vivo.

## Estado atual

- Laravel 13, React, Inertia e Tailwind;
- Fluxo público de criação e entrada em equipes;
- Consulta da matrícula na base local do Campus;
- Regras de vagas (15 no total, 10 para Engenharia Civil e 5 para ampla concorrência), matrícula única e equipes de 2 a 5 participantes;
- Migrations e dados fictícios para homologação.

## Executar localmente

1. Inicie o MySQL pelo XAMPP e crie o banco `spino_central`.
2. Copie `.env.example` para `.env` ou ajuste as credenciais MySQL existentes nesse arquivo.
3. Execute `php artisan migrate --seed`.
4. Em um terminal, execute `php artisan serve`; em outro, `npm run dev`.
5. Abra `http://127.0.0.1:8000`.

As matrículas fictícias `CT300001`, `CT300002` e `CT300003` podem ser usadas para validar o fluxo. A integração com a base oficial do Campus substituirá esta tabela local antes da implantação.

## Próximas entregas

- painel administrativo de inscrições e auditoria;
- controle de janela de inscrição pela administração;
- autenticação e perfis da organização;
- API de telemetria, eventos e Laravel Reverb;
- votação dos juízes e dashboard público.
