<?php

namespace qa;

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

use function Castor\fs;
use function Castor\io;
use function Castor\parallel;

#[AsTask(description: 'Run all QA tools', aliases: ['qa'])]
function all(bool $parallel = false): int
{
    io()->title('Running all QA tools');

    if ($parallel) {
        [$rector, $phpstan, $phpcs, $lint, $pint] = parallel(
            fn (): ?Process => rector(),
            fn (): ?Process => phpstan(),
            fn (): ?Process => phpcs(),
            fn (): ?Process => lint(),
            fn (): ?Process => pint(),
        );
    } else {
        $rector = rector();
        $phpstan = phpstan();
        $phpcs = phpcs();
        $lint = lint();
        $pint = pint();
    }

    return max(
        $rector?->getExitCode() ?? 0,
        $phpstan?->getExitCode() ?? 0,
        $phpcs?->getExitCode() ?? 0,
        $lint?->getExitCode() ?? 0,
        $pint?->getExitCode() ?? 0,
    );
}

#[AsTask(description: 'Run PHPStan', aliases: ['phpstan'])]
function phpstan(): ?Process
{
    $binary = 'vendor/bin/phpstan';

    if (!fs()->exists(buildLocalPath($binary))) {
        io()->warning('Binary phpstan not found');

        return null;
    }

    io()->title('Running PHPStan');

    return \docker\exec(SERVICE_PHP, [$binary, 'analyse', '--memory-limit', '256M']);
}

#[AsTask(description: 'Run Rector', aliases: ['rector'])]
function rector(
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

#[AsTask(description: 'Run PHP-CS-Fixer', aliases: ['phpcs', 'cs'])]
function phpcs(
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

    io()->title('Running PHP-CS-Fixer'.($fix ? '' : ' (**dry-run**)'));

    return \docker\exec(SERVICE_PHP, $cmd);
}

#[AsTask(description: 'Lint Twig templates', aliases: ['lint'])]
function lint(): ?Process
{
    if (!fs()->exists(buildLocalPath('vendor/symfony/twig-bundle'))) {
        io()->warning('Twig bundle not found');

        return null;
    }

    io()->title('Running Twig Linter');

    return \docker\exec(SERVICE_PHP, ['php', 'bin/console', 'lint:twig', '--show-deprecations', 'templates/']);
}

#[AsTask(description: 'Run Pint', aliases: ['pint'])]
function pint(): ?Process
{
    $binary = 'vendor/bin/pint';

    if (!fs()->exists(buildLocalPath($binary))) {
        io()->warning('Binary pint not found');

        return null;
    }

    $cmd = [$binary, 'app/', '--test', '-v'];

    return \docker\exec(SERVICE_PHP, $cmd);
}

function buildLocalPath(string $binary): string
{
    return __DIR__ . '/../' . CODE_PATH . $binary;
}
