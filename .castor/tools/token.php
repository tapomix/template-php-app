<?php

namespace tools\token;

use Castor\Attribute\AsTask;

use function Castor\io;

#[AsTask(description: 'Generate a random token', aliases: ['token'])]
function generator(int $length = 32): void
{
    $token = bin2hex(random_bytes($length));

    io()->text("Copy+Paste your new token : $token");
}
