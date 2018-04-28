<?php

namespace Sikhlana\Backup\Commands;

use Sikhlana\Backup\Concerns\FetchesHomeDirectory;
use Sikhlana\Backup\Concerns\FetchesKeyFileContents;
use Symfony\Component\Console\Command\Command;

class Restore extends Command
{
    use FetchesKeyFileContents, FetchesHomeDirectory;
}