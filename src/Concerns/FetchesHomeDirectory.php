<?php

namespace App\Concerns;

use App\Exceptions\HomeDirectoryException;

trait FetchesHomeDirectory
{
    protected function getHomeDirectory($write = false)
    {
        $home = getenv('HOME');
        $home = trim(ltrim($home, DIRECTORY_SEPARATOR));

        if (empty($home)) {
            throw new HomeDirectoryException('Unable to determine the home directory for the user.');
        }

        if ($write) {
            if (! is_writable($home)) {
                throw new HomeDirectoryException('Will not be able to write key file to the user\'s home directory.');
            }
        } else {
            if (! is_readable($home)) {
                throw new HomeDirectoryException('Will not be able to read key file from the user\'s home directory.');
            }
        }

        return $home;
    }
}