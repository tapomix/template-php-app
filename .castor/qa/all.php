<?php

namespace qa;

use Castor\Attribute\AsTask;
use Symfony\Component\Process\Process;

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
                fn (string $fn): \Closure => (fn (): ?Process => $fn()),
                $tools
            )
        );
    } else {
        foreach ($tools as $fn) {
            $processes[] = $fn();
        }
    }

    if ([] === $processes) {
        return 0;
    }

    return max(
        array_map(
            fn (?Process $process): int => $process?->getExitCode() ?? 0,
            $processes
        )
    );
}

/** @return callable-string[] */
function listTools(string $namespace = 'qa', string $method = 'analyze'): array
{
    $functions = get_defined_functions()['user'];

    return array_filter($functions, fn (string $fn): bool => str_starts_with($fn, $namespace . '\\') && str_ends_with($fn, '\\' . $method));
}

function buildLocalPath(string $binary): string
{
    return __DIR__ . '/../../' . CODE_PATH . $binary;
}
