<?php

namespace git\template;

use Castor\Attribute\AsTask;

use function Castor\capture;
use function Castor\io;
use function Castor\run;

define('TEMPLATE_REMOTE_NAME', 'template');
define('TEMPLATE_URL_GITHUB', 'https://github.com/tapomix/template-php-site');

#[AsTask(description: 'Add remote for the template', aliases: ['template:add'])]
function add(string $remote = TEMPLATE_REMOTE_NAME): void
{
    if (null === findRemoteTemplateName()) {
        run(\sprintf('git remote add %s %s', $remote, TEMPLATE_URL_GITHUB));
        run('git remote set-url --push ' . $remote . ' NO_PUSH'); // set a fake url to disable push on remote
        io()->success('Remote added');
    } else {
        io()->error('Remote for template already exist');
    }
}

#[AsTask(description: 'Compare template with this project', aliases: ['template:compare'])]
function compare(): void
{
    execDiff(true);
}

#[AsTask(description: 'Display differences between template vs project', aliases: ['template:diff'])]
function diff(): void
{
    execDiff(false);
}

function execDiff(bool $withStat): void
{
    $remote = findRemoteTemplateName();

    if (null === $remote) {
        io()->warning('Template remote is missing, add it with: castor git:template:add');

        return;
    }

    $cmd = \array_merge(
        ['git', 'diff', 'HEAD'],
        $withStat ? ['--stat'] : [],
        [$remote . '/main', '--', '.'],
    );

    // ignore these files that always contain differences
    $excluded = [
        '**/devcontainer.json',
        '.gitmodules',
        CODE_PATH,
    ];

    foreach ($excluded as $path) {
        $cmd[] = ':(exclude)' . $path;
    }

    // first update template ...
    run('git fetch ' . $remote);
    // ... then compare
    run($cmd);
}

function findRemoteTemplateName(): ?string
{
    $remote = \trim(capture(\sprintf("git remote -v | grep '%s' | awk '{print \$1}' | uniq", TEMPLATE_URL_GITHUB)));

    return '' !== $remote ? $remote : null;
}
