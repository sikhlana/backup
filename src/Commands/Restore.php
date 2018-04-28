<?php

namespace App\Commands;

use App\Concerns\FetchesHomeDirectory;
use App\Concerns\FetchesKeyFileContents;
use Symfony\Component\Console\Command\Command;

class Restore extends Command
{
    use FetchesKeyFileContents, FetchesHomeDirectory;
}