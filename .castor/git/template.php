<?php

namespace git\template;

use Castor\Attribute\AsTask;

use function Castor\capture;
use function Castor\io;
use function Castor\run;

define('TEMPLATE_REMOTE_NAME', 'template');
define('TEMPLATE_URL_GITHUB', 'https://github.com/tapomix/template-php-site');

#[AsTask(description: 'Add remote for the template', aliases: ['template:add'])]
function add(string $remote = TEMPLATE_REMOTE_NAME, string $url = TEMPLATE_URL_GITHUB): void
{
    if (null === findRemoteTemplateName()) {
        run(\sprintf('git remote add %s %s', $remote, $url));
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
    $cmd = \array_merge(
        ['git', 'diff'],
        $withStat ? ['--stat'] : [],
        ['template/main', '--', '.'],
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

    $remote = findRemoteTemplateName();

    if (null !== $remote) {
        // first update template ...
        run('git fetch ' . $remote);
        // ... then compare
        run($cmd);
    } else {
        io()->warning('Template remote is missing, add it with: castor git:template:add');
    }
}

function findRemoteTemplateName(string $url = TEMPLATE_URL_GITHUB): ?string
{
    $remote = \trim(capture(\sprintf("git remote -v | grep '%s' | awk '{print \$1}' | uniq", $url)));

    return \strlen($remote) > 0 ? $remote : null;
}
