<?php

if (!defined("DIRSEP")) {
    define("DIRSEP", DIRECTORY_SEPARATOR);
}

class Magmi_StateManager
{
    private static $_statefile = null;
    private static $_script = __FILE__;
    private static $_state = "idle";

    public static function getStateFile()
    {
        return self::getStateDir() . DIRSEP . "magmistate";
    }

    public static function getTraceFile()
    {
        return self::getStateDir() . DIRSEP . "trace.txt";
    }

    public static function getStateDir()
    {
        return dirname(dirname(self::$_script)) . DIRSEP . "state";
    }

    public static function getProgressFile($full = false)
    {
        $fullname = self::getStateDir() . DIRSEP . "progress.txt";
        $pfname = ($full ? $fullname : "progress.txt");
        return $pfname;
    }

    public static function clearTrace()
    {
        $f = @fopen(self::getTraceFile(), "w");
        if ($f === false) {
            return false;
        }
        fclose($f);
        @chmod(self::getTraceFile(), 0664);
        return true;
    }

    public static function setState($state, $force = false)
    {
        if (self::$_state == $state && !$force) {
            return;
        }

        self::$_state = $state;
        $f = fopen(self::getStateFile(), "w");
        fwrite($f, self::$_state);
        fclose($f);
        @chmod(self::getStateFile(), 0664);
        if ($state == "running") {
            self::clearTrace();
        }
    }

    public static function getState($cached = false)
    {
        if (!$cached) {
            if (!file_exists(self::getStateFile())) {
                self::setState("idle", true);
            }
            $state = file_get_contents(self::getStateFile());
        } else {
            $state = self::$_state;
        }
        return $state;
    }
}
