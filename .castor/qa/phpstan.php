<?php

namespace qa\phpstan;

use Castor\Attribute\AsTask;
use Symfony\Component\Process\Process;

use function Castor\fs;
use function Castor\io;
use function qa\buildLocalPath;

#[AsTask(description: 'Run PHPStan', aliases: ['phpstan'])]
function analyze(): ?Process
{
    $binary = 'vendor/bin/phpstan';

    if (!fs()->exists(buildLocalPath($binary))) {
        io()->warning('Binary phpstan not found');

        return null;
    }

    io()->title('Running PHPStan');

    return \docker\exec(SERVICE_PHP, [$binary, 'analyse', '--memory-limit', '256M']);
}
