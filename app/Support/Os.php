<?php

namespace App\Support;

class Os
{
    public static function isSystemPath($basename)
    {
        return in_array($basename, [
            'System Volume Information',
            '$RECYCLE.BIN',
        ]);
    }
}