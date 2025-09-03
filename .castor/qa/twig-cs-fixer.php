<?php

namespace qa\twigcsfixer;

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

use function Castor\fs;
use function Castor\io;

use function qa\buildLocalPath;

#[AsTask(description: 'Run Twig-CS-Fixer', aliases: ['twig-cs'])]
function analyze(
    #[AsOption(shortcut: 'f', description: 'Really fix issues', mode: InputOption::VALUE_NEGATABLE)]
    bool $fix = false,
): ?Process {
    $binary = 'vendor/bin/twig-cs-fixer';

    if (!fs()->exists(buildLocalPath($binary))) {
        io()->warning('Binary twig-cs-fixer not found');

        return null;
    }

    $cmd = [$binary, 'lint', '--debug'];
    if ($fix) {
        $cmd[] = '--fix';
    }

    io()->title('Running Twig-CS-Fixer'.($fix ? '' : ' (**dry-run**)'));

    return \docker\exec(SERVICE_PHP, $cmd);
}
