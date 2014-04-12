<?php namespace Mosaicpro\WpCore;

/**
 * Class Date
 * @package Mosaicpro\WpCore
 */
class Date
{
    /** Create a new Date instance
     * @param $name
     * @param $args
     */
    public function __construct($name, $args)
    {
        return call_user_func_array([$this, $name], $args);
    }

    /**
     * Handle the magic
     * @param $name
     * @param $args
     * @return static
     */
    public static function __callStatic($name, $args)
    {
        return new static($name, $args);
    }

    /**
     * Helper method to convert hh:mm:ss to seconds
     * @param $time
     * @return mixed
     */
    public static function time_to_seconds($time)
    {
        $parsed = date_parse($time);
        $seconds = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];
        return $seconds;
    }

    /**
     * Helper method to convert seconds in hh:mm:ss
     * @param $seconds
     * @return string
     */
    public static function seconds_to_time($seconds)
    {
        $t = round($seconds);
        $hours = $t/3600;
        $minutes = $t/60%60;
        $seconds = $t%60;

        if ($hours < 1 && $minutes < 1)
            $time = sprintf('%d seconds', $seconds);
        if($hours < 1 && $seconds < 1)
            $time = sprintf('%d minutes', $minutes);
        if($hours < 1 && $seconds > 0)
            $time = sprintf('%d minutes %d seconds', $minutes, $seconds);
        if($minutes < 1 && $seconds == 0)
            $time = sprintf('%d hours', $hours);
        if($hours > 0 && $minutes > 0 && $seconds > 0)
            $time = sprintf('%dh %dm %ds', $hours, $minutes, $seconds);

        return $time;
    }

    /**
     * Helper method to convert hh:mm:ss to a more human friendly format
     * e.g. xx hours / xx hours xx minutes / xx minutes xx seconds / etc
     * @param $time
     * @return string
     */
    public static function time_format($time)
    {
        return self::seconds_to_time(self::time_to_seconds($time));
    }
} 