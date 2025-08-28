<?php

namespace symfony;

use Castor\Attribute\AsArgument;
use Castor\Attribute\AsTask;

use function Castor\context;

/** @param string $cmd */
#[AsTask(description: 'Excute console command', aliases: ['console'])]
function console(
    #[AsArgument(description: 'Command to execute')]
    ?string $cmd = null,
): void
{
    \docker\run(SERVICE_PHP, \array_merge(['php', 'bin/console'], [(string) $cmd]), context()->withAllowFailure());
}
