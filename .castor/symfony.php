<?php

namespace symfony;

use Castor\Attribute\AsRawTokens;
use Castor\Attribute\AsTask;

use function Castor\context;

/** @param string[] $args */
#[AsTask(description: 'Execute a Symfony Console command', aliases: ['console'])]
function console(
    #[AsRawTokens]
    array $args = [],
): void {
    \docker\exec(SERVICE_PHP, \array_merge(['php', 'bin/console'], $args), context('interactive'));
}
