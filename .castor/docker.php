<?php

namespace docker;

use Castor\Attribute\AsTask;
use Castor\Console\Output\VerbosityLevel;
use Castor\Context;
use Castor\Exception\ProblemException;
use Symfony\Component\Process\Process;

use function Castor\capture;
use function Castor\context;
use function Castor\fs;
use function Castor\io;
use function Castor\run as castor_run;

#[AsTask(description: 'Build server (aka compose up --build)', aliases: ['build'])]
function build(): void
{
    io()->title('Building server');

    castor_run(\array_merge(buildBaseDockerComposeCmd(), ['build', '--no-cache']), context: context()->withVerbosityLevel(VerbosityLevel::VERBOSE));
}

#[AsTask(description: 'Start server (aka compose up)', aliases: ['start', 'up'])]
function start(): void
{
    io()->title('Starting server');

    capture(\array_merge(buildBaseDockerComposeCmd(), ['up', '--detach', '--wait']));
}

#[AsTask(description: 'Stopping server (aka compose down)', aliases: ['stop', 'down'])]
function stop(): void
{
    io()->title('Stopping server');

    capture(\array_merge(buildBaseDockerComposeCmd(), ['down', '--remove-orphans']));
}

#[AsTask(description: 'Show server logs', aliases: ['logs'])]
function logs(): void
{
    io()->title('Showing server logs');

    castor_run(\array_merge(buildBaseDockerComposeCmd(), ['logs', '-f']));
}

#[AsTask(description: 'Open terminal in container', aliases: ['sh'])]
function shell(string $service): void
{
    \docker\exec($service, ['bash'], context()->withTty(true));
}

/** @return string[] */
function buildBaseDockerComposeCmd(): array
{
    $envCompose = 'compose.' . $_SERVER['APP_ENV'] . '.yaml';

    if (!fs()->exists($envCompose)) {
        throw new ProblemException('Specific Docker Compose not found');
    }

    if (!fs()->exists(DOCKER_ENV)) {
        throw new ProblemException('Docker Compose config not found');
    }

    // ! prod ! ensure the file always exist to use as secret
    $composerAuthFile = '.docker/.composer-auth.json';
    if (
        'prod' === $_SERVER['APP_ENV']
        && !fs()->exists($composerAuthFile)
    ) {
        fs()->dumpFile($composerAuthFile, '{}');
    }

    return [
        'docker',
        'compose',

        '-f',
        'compose.yaml',
        '-f',
        $envCompose,

        '--env-file=' . DOCKER_ENV,
    ];
}

/** @param string[] $command */
function run(string $service, array $command, ?Context $context = null): Process
{
    $context ??= context();

    return castor_run(\array_merge(buildBaseDockerComposeCmd(), ['run', '--rm'], [$service], $command), context: $context);
}

/** @param string[] $command */
function exec(string $service, array $command, ?Context $context = null): Process
{
    $context ??= context();

    return castor_run(\array_merge(buildBaseDockerComposeCmd(), ['exec'], [$service], $command), context: $context);
}
