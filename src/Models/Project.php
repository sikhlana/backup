<?php

namespace Sikhlana\Backup\Models;

use League\Flysystem\Filesystem;
use Sikhlana\Backup\Concerns\CreatesFilesystem;

class Project extends Model
{
    use CreatesFilesystem;

    const PROJECT_JSON_FILENAME = '.gfnbak';

    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $target;

    /**
     * @var Filesystem
     */
    protected $fs;

    public function setSourceDirectory($source)
    {
        $this->source = $source;
        $this->fs = $this->createLocalFilesystem($source);

        return $this;
    }

    public function setTargetDirectory($target)
    {
        $this->target = $target;

        return $this;
    }

    public function getPathForDatabaseDump($name)
    {
        return sprintf('%s/%s/databases/%s', $this->target, $this->getAttribute('name'), $name);
    }
}