# Spino Central

Aplicação web do Módulo 3 do Projeto Spino, voltada à operação do Concurso de Pontes de Palito do IFSP Caraguatatuba. A plataforma centraliza inscrições, organização do evento, avaliação dos juízes e divulgação pública dos resultados.

## Tecnologias

- PHP 8.3+ e Laravel 13;
- React 18 com Inertia.js;
- Tailwind CSS e Vite;
- SQLite;
- Laravel Sanctum, Breeze e Ziggy;
- Docker e Docker Compose para implantação.

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
- Extensão SQLite do PHP.

### Configuração

1. Clone o repositório e acesse a pasta do projeto.
2. Instale as dependências:

   ```bash
   composer install
   npm install
   ```

3. Crie o banco SQLite, o arquivo de ambiente e a chave da aplicação:

   ```bash
   touch database/database.sqlite
   cp .env.example .env
   php artisan key:generate
   ```

4. No `.env`, use o caminho local do SQLite:

   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=/caminho/absoluto/para/spino-web/database/database.sqlite
   ```

5. Configure as credenciais SMTP para a confirmação de e-mail e execute as migrations:

   ```bash
   php artisan migrate --seed
   ```

6. Inicie a aplicação e o servidor de desenvolvimento do front-end:

   ```bash
   php artisan serve
   npm run dev
   ```

A aplicação estará disponível em `http://127.0.0.1:8000`.

## Implantação com Docker Compose

O ambiente de produção usa apenas o serviço `app`. O arquivo SQLite fica em `./data/spino.sqlite` no servidor e não é incluído na imagem nem versionado no Git.

1. No servidor, clone o repositório e crie o arquivo de ambiente:

   ```bash
   cp .env.example .env
   ```

2. Ajuste o `.env` para produção. No mínimo:

   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://seu-dominio
   DB_CONNECTION=sqlite
   DB_DATABASE=/var/www/html/storage/app/spino.sqlite
   MAIL_MAILER=smtp
   MAIL_HOST=servidor-smtp
   MAIL_PORT=587
   MAIL_USERNAME=usuario
   MAIL_PASSWORD=senha-ou-app-password
   MAIL_FROM_ADDRESS=remetente@dominio
   SENSORS_API_TOKEN=gere-um-token-longo-e-aleatorio
   ```

   Gere uma chave e copie o resultado para `APP_KEY`:

   ```bash
   docker compose run --rm app php artisan key:generate --show
   ```

3. Suba a aplicação:

   ```bash
   docker compose up --build -d
   ```

Por padrão, o Compose publica a aplicação somente em `127.0.0.1:8080`; o servidor deve encaminhar o domínio por um proxy reverso com HTTPS. Caso o proxy use outra porta, altere `SPINO_HTTP_PORT`. Não exponha o SQLite na rede e mantenha somente uma réplica da aplicação.

Faça backup regular de `data/spino.sqlite` no próprio servidor. SQLite atende bem ao volume deste evento, mas não deve ser colocado em diretório compartilhado de rede nem usado por múltiplos containers da aplicação.


## Telemetria de ensaios (M2 → M3)

Antes de iniciar a ruptura, a comissão abre **Administração → Ensaios**, seleciona a equipe e arma o M2 (por exemplo, `M2-01`). Isso cria um `run_id` único e auditável.

O Raspberry consulta o ensaio atual:

```
GET /api/v1/sensors/devices/M2-01/active-run
Authorization: Bearer <SENSORS_API_TOKEN>
```

Com um ensaio ativo, cada leitura deve enviar `test_run_id`, `device_code` e um `sequence` crescente. O servidor rejeita leituras de outro M2, ensaios encerrados e sequências reutilizadas com dados diferentes.

```json
POST /api/v1/sensors/capture
{
  "test_run_id": "uuid-do-ensaio",
  "device_code": "M2-01",
  "sequence": 42,
  "event": "sample",
  "elapsed_ms": 1250,
  "load_grams": 3200,
  "timestamp": "2026-10-22T13:00:00-03:00"
}
```

Use `event: "completed"` na última leitura. Se a telemetria falhar, registre a contingência em **Rupturas** após armar o ensaio; a leitura automática continua tendo precedência no placar.
