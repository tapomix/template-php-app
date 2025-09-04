<?php

namespace tools\dbgate;

use Castor\Attribute\AsTask;

use function Castor\open as castor_open;

#[AsTask(description: 'Open DBGate instance', aliases: ['dbgate'])]
function open(): void
{
    castor_open('http://localhost:' . ($_SERVER['DBGATE_PORT'] ?? 8800));
}
