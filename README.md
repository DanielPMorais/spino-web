# Spino Central

Aplicação web do Módulo 3 do Projeto Spino, voltada à operação do Concurso de Pontes de Palito do IFSP Caraguatatuba. A plataforma centraliza inscrições, organização do evento, avaliação dos juízes e divulgação pública dos resultados.

## Tecnologias

- PHP 8.3 e Laravel 13;
- React 18 com Inertia.js;
- Tailwind CSS e Vite;
- MySQL ou PostgreSQL;
- Laravel Sanctum, Breeze e Ziggy.

## Funcionalidades

### Área pública

- Criação de equipes e ingresso por código;
- Confirmação de e-mail acadêmico por código de seis dígitos;
- Controle da janela de inscrições, limite de vagas, e-mail único e equipes de 2 a 5 integrantes;
- Páginas de regulamento, cronograma e contato;
- Placar público com classificação geral, eficiência, precisão e estética;
- Visualização do ensaio em andamento e gráfico de carga × tempo da ruptura.

### Organização do evento

- Autenticação e perfis da organização;
- Painel administrativo para acompanhar e aprovar inscrições;
- Auditoria das pontes inscritas;
- Cadastro de juízes e controle da janela de votação;
- Painel individual do juiz acessado por credencial para registrar votos.

## Executar localmente

### Pré-requisitos

- PHP 8.3 ou superior;
- Composer;
- Node.js e npm;
- MySQL ou PostgreSQL.

### Configuração

1. Clone o repositório e acesse a pasta do projeto.
2. Instale as dependências:

   ```bash
   composer install
   npm install
   ```

3. Crie o arquivo de ambiente e gere a chave da aplicação:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure no `.env` a conexão com o banco de dados e, para a confirmação de e-mail, as credenciais de envio de e-mail.
5. Execute as migrations e dados de homologação:

   ```bash
   php artisan migrate --seed
   ```

6. Inicie a aplicação e o servidor de desenvolvimento do front-end:

   ```bash
   php artisan serve
   npm run dev
   ```

A aplicação estará disponível em `http://127.0.0.1:8000`.

## Próximos passos

- Receber telemetria diretamente dos módulos físicos do Spino;
- Publicar atualizações do ensaio em tempo real;
- Consolidar histórico de eventos, medições e resultados para consulta posterior.
