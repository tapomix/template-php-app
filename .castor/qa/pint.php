<?php

namespace qa\pint;

use Castor\Attribute\AsTask;
use Symfony\Component\Process\Process;

use function Castor\fs;
use function Castor\io;
use function qa\buildLocalPath;

#[AsTask(description: 'Run Pint', aliases: ['pint'])]
function analyze(): ?Process
{
    $binary = 'vendor/bin/pint';

    if (!fs()->exists(buildLocalPath($binary))) {
        io()->warning('Binary pint not found');

        return null;
    }

    $cmd = [$binary, 'app/', '--test', '-v'];

    return \docker\exec(SERVICE_PHP, $cmd);
}
