<?php

namespace App\Services;

class Time
{
    /**
     * Perform timecode to seconds transformation
     *
     * @param [type] $time
     * @return void
     */
    public static function toSeconds($timecode): int
    {
        if (preg_match("#\d{1,2}:\d{1,2}:\d{1,2}#i", $timecode)) {
            list($h, $m, $s) = sscanf($timecode, '%d:%d:%d');
            return $h * 3600 + $m * 60 + $s;
        } else if (preg_match("#\d{1,2}:\d{1,2}#i", $timecode)) {
            list($m, $s) = sscanf($timecode, '%d:%d');
            return $m * 60 + $s;
        }
        return 0;
    }
}
