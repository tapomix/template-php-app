<?php

namespace qa\rector;

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

use function Castor\fs;
use function Castor\io;

use function qa\buildLocalPath;

#[AsTask(description: 'Run Rector', aliases: ['rector'])]
function analyze(
    #[AsOption(shortcut: 'f', description: 'Really fix issues', mode: InputOption::VALUE_NEGATABLE)]
    bool $fix = false,
): ?Process {
    $binary = 'vendor/bin/rector';

    if (!fs()->exists(buildLocalPath($binary))) {
        io()->warning('Binary rector not found');

        return null;
    }

    $cmd = [$binary, 'process'];
    if (!$fix) {
        $cmd = \array_merge($cmd, ['--dry-run', '--debug']);
    }

    io()->title('Running Rector'.($fix ? '' : ' (**dry-run**)'));

    return \docker\exec(SERVICE_PHP, $cmd);
}
