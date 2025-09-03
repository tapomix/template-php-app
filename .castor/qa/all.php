<?php

namespace qa;

use Castor\Attribute\AsTask;

use function Castor\io;
use function Castor\parallel;

#[AsTask(description: 'Run all QA analyzers', aliases: ['qa'])]
function all(bool $parallel = false): int
{
    io()->title('Running all analyzers ');

    $tools = listTools();

    if ([] === $tools) {
        io()->warning('No QA analyzer found');

        return 0;
    }

    $processes = [];

    if ($parallel) {
        $processes = parallel(
            ...array_map(
                fn (string $fn) => function () use ($fn): ?Process {
                    return $fn();
                },
                $tools
            )
        );
    } else {
        foreach ($tools as $fn) {
            $processes[] = $fn();
        }
    }

    return max(
        array_map(
            fn (?Process $process) => $process?->getExitCode() ?? 0,
            $processes
        )
    );
}

/** @return string[] */
function listTools(string $namespace = 'qa', string $method = 'analyze'): array
{
    $functions = get_defined_functions()['user'];

    return array_filter($functions, fn (string $fn) => str_starts_with($fn, $namespace . '\\') && str_ends_with($fn, '\\' . $method));
}

function buildLocalPath(string $binary): string
{
    return __DIR__ . '/../../' . CODE_PATH . $binary;
}
