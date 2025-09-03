<?php

namespace tools\mailpit;

use Castor\Attribute\AsTask;

use function Castor\open as castor_open;

#[AsTask(description: 'Open Mailpit instance', aliases: ['mailpit'])]
function open(): void
{
    castor_open('http://localhost:'.($_SERVER['MAILPIT_PORT'] ?? 8525));
}
