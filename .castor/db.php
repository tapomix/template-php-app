<?php

namespace db;

use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\io;

#[AsTask(description: 'Connect into PosgreSQL database', aliases: ['db', 'pg'])]
function database(): void
{
    io()->title('Connecting into PosgreSQL database');

    \docker\exec(SERVICE_DB, ['psql', '-U', $_SERVER['DB_USER'] ?? 'pg', $_SERVER['DB_NAME'] ?? 'app'], context: context()->toInteractive());
}

#[AsTask(description: 'Backup database')]
function backup(string $timing): void
{
    io()->title('Create DB backup');

    $dumpFile = '/sql/dump-' . $_SERVER['CONTAINER_NAME'] . '-' . $timing . '.sql';

    \docker\exec(SERVICE_DB, ['pg_dump', '-O', '-U', $_SERVER['DB_USER'] ?? 'pg', '-f', $dumpFile, $_SERVER['DB_NAME']]);
}
