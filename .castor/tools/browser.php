<?php

namespace tools\browser;

use Castor\Attribute\AsTask;

use function Castor\open as castor_open;

#[AsTask(description: 'Open app in browser', aliases: ['open', 'browser'])]
function open(): void
{
    castor_open('https://' . ($_SERVER['SERVER_NAME'] ?? 'localhost') . ':' . ($_SERVER['HTTPS_PORT'] ?? 443));
}
