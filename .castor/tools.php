<?php

namespace tools;

use Castor\Attribute\AsTask;

use function Castor\open;

// @see https://stackoverflow.com/a/75956168 (to fix vscode settings)

#[AsTask(description: 'Open browser', aliases: ['open', 'browse'])]
function browse(): void
{
    open('https://'.($_SERVER['SERVER_NAME'] ?? 'localhost').':'.($_SERVER['HTTPS_PORT'] ?? 443));
}

#[AsTask(description: 'Open Mailpit instance', aliases: ['mailpit'])]
function mailpit(): void
{
    open('http://localhost:'.($_SERVER['MAILPIT_PORT'] ?? 8525));
}

#[AsTask(description: 'Open DBGate instance', aliases: ['dbgate'])]
function dbgate(): void
{
    open('http://localhost:'.($_SERVER['DBGATE_PORT'] ?? 8800));
}
