<?php

namespace db;

use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\fs;
use function Castor\io;

const DEFAULT_DB_USER = 'pg';
const DEFAULT_DB_NAME = 'app';

#[AsTask(description: 'Connect into PosgreSQL database', aliases: ['db', 'pg'])]
function database(): void
{
    io()->title('Connecting into PosgreSQL database');

    \docker\exec(SERVICE_DB, ['psql', '-U', $_SERVER['DB_USER'] ?? DEFAULT_DB_USER, $_SERVER['DB_NAME'] ?? DEFAULT_DB_NAME], context: context()->toInteractive());
}

#[AsTask(description: 'Backup database')]
function backup(string $timing): void
{
    io()->title('Create DB backup');

    \docker\exec(SERVICE_DB, ['pg_dump', '-O', '-U', $_SERVER['DB_USER'] ?? DEFAULT_DB_USER, '-f', \db\buildDumpFileName($timing), $_SERVER['DB_NAME'] ?? DEFAULT_DB_NAME]);
}

function buildDumpFileName(string $timing, bool $local = false): string
{
    $dumpName = '/dump-' . $_SERVER['CONTAINER_NAME'] . '-' . $timing . '.sql';

    return $local
        ? __DIR__ . '/../.docker/db/sql' . $dumpName
        : '/sql' . $dumpName
    ;
}

#[AsTask(description: 'Restore database')]
function restore(string $timing): void
{
    io()->title('Restore DB backup');

    if (!fs()->exists(\db\buildDumpFileName($timing, true))) {
        io()->error('Dump not found');

        return;
    }

    \docker\exec(SERVICE_DB, ['dropdb', '-U', $_SERVER['DB_USER'] ?? DEFAULT_DB_USER, $_SERVER['DB_NAME'] ?? DEFAULT_DB_NAME]);
    \docker\exec(SERVICE_DB, ['createdb', '-U', $_SERVER['DB_USER'] ?? DEFAULT_DB_USER, $_SERVER['DB_NAME'] ?? DEFAULT_DB_NAME]);
    \docker\exec(SERVICE_DB, ['psql', '-U', $_SERVER['DB_USER'] ?? DEFAULT_DB_USER, '-f', \db\buildDumpFileName($timing), $_SERVER['DB_NAME'] ?? DEFAULT_DB_NAME]);

    io()->success('Dump ' . $timing . ' loaded');
}
