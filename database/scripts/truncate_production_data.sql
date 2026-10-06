-- PostgreSQL / Render: remove todos os dados de aplicação e preserva a tabela
-- "migrations", para que o próximo deploy não tente recriar o esquema.
-- Atenção: esta operação é irreversível.

DO $$
DECLARE
    tables_to_truncate text;
BEGIN
    SELECT string_agg(format('%I.%I', schemaname, tablename), ', ')
    INTO tables_to_truncate
    FROM pg_tables
    WHERE schemaname = 'public'
      AND tablename <> 'migrations';

    IF tables_to_truncate IS NOT NULL THEN
        EXECUTE 'TRUNCATE TABLE ' || tables_to_truncate || ' RESTART IDENTITY CASCADE';
    END IF;
END $$;
