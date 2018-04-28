<?php

namespace App\Concerns;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

trait CreatesFilesystem
{
    protected function createLocalFilesystem($path)
    {
        return new Filesystem(new Local($path));
    }
}