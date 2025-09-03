<?php

namespace qa\lint;

use Castor\Attribute\AsTask;
use Symfony\Component\Process\Process;

use function Castor\fs;
use function Castor\io;

use function qa\buildLocalPath;

#[AsTask(description: 'Lint Twig templates', aliases: ['lint'])]
function analyze(): ?Process
{
    if (!fs()->exists(buildLocalPath('vendor/symfony/twig-bundle'))) {
        io()->warning('Twig bundle not found');

        return null;
    }

    io()->title('Running Twig Linter');

    return \docker\exec(SERVICE_PHP, ['php', 'bin/console', 'lint:twig', '--show-deprecations', 'templates/']);
}
