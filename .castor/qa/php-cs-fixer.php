<?php

namespace qa\phpcsfixer;

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

use function Castor\fs;
use function Castor\io;
use function qa\buildLocalPath;

#[AsTask(description: 'Run PHP-CS-Fixer', aliases: ['php-cs', 'cs'])]
function analyze(
    #[AsOption(shortcut: 'f', description: 'Really fix issues', mode: InputOption::VALUE_NEGATABLE)]
    bool $fix = false,
): ?Process {
    $binary = 'vendor/bin/php-cs-fixer';

    if (!fs()->exists(buildLocalPath($binary))) {
        io()->warning('Binary php-cs-fixer not found');

        return null;
    }

    $cmd = [$binary, 'fix'];
    if (!$fix) {
        $cmd = \array_merge($cmd, ['--dry-run', '-vv', '--diff', '--show-progress=dots']);
    }

    io()->title('Running PHP-CS-Fixer' . ($fix ? '' : ' (**dry-run**)'));

    return \docker\exec(SERVICE_PHP, $cmd);
}
