<?php

/**
 * Sends statistics to the stats daemon over UDP
 *
 * @category Tools
 * @package  StatsD
 * @author   Etsy
 * @author   Till Klampaeckel <till@php.net>
 * @license  https://github.com/etsy/statsd/blob/master/LICENSE New BSD License
 */
class StatsD
{
    /**
     * @var array $config
     */
    protected static $config;

    /**
     * Pass in configuration, supply defaults if necessary.
     *
     * @param array $config
     *
     * @return void
     * @throws InvalidArgumentException When 'enabled' is missing.
     */
    public static function init(array $config)
    {
        if (!isset($config['enabled'])) {
            throw new InvalidArgumentException("Config must contain 'enabled' flag.");
        }
        if ($config['enabled']) {
            if (!isset($config['port']) || empty($config['port'])) {
                $config['port'] = 8125;
            }
            if (!isset($config['host']) || empty($config['host'])) {
                $config['host'] = '127.0.0.1';
            }
        }
        self::$config = $config;
    }

    /**
     * Log timing information
     *
     * @param string $stats The metric to in log timing info for.
     * @param float $time The ellapsed time (ms) to log
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     *
     * @return boolean
     */
    public static function timing($stat, $time, $sampleRate=1)
    {
        return self::send(array($stat => "$time|ms"), $sampleRate);
    }

    /**
     * Increments one or more stats counters
     *
     * @param string|array $stats The metric(s) to increment.
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     * @return boolean
     **/
    public static function increment($stats, $sampleRate=1)
    {
        return self::updateStats($stats, 1, $sampleRate);
    }

    /**
     * Decrements one or more stats counters.
     *
     * @param string|array $stats The metric(s) to decrement.
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     * @return boolean
     **/
    public static function decrement($stats, $sampleRate=1)
    {
        return self::updateStats($stats, -1, $sampleRate);
    }

    /**
     * Updates one or more stats counters by arbitrary amounts.
     *
     * @param string|array $stats The metric(s) to update. Should be either a string or array of metrics.
     * @param int|1 $delta The amount to increment/decrement each metric by.
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     *
     * @return boolean
     */
    public static function updateStats($stats, $delta=1, $sampleRate=1)
    {
        if (!is_array($stats)) {
            $stats = array($stats);
        }
        $data = array();
        foreach($stats as $stat) {
            $data[$stat] = "$delta|c";
        }

        return self::send($data, $sampleRate);
    }

    /*
     * Squirt the metrics over UDP
     *
     * @param array $data
     * @param float|1 $sampleRate
     *
     * @return boolean
     */
    public static function send(array $data, $sampleRate=1)
    {
        if (!self::$config['enabled']) {
            return false;
        }

        // sampling
        $sampledData = array();

        if ($sampleRate < 1) {
            foreach ($data as $stat => $value) {
                if ((mt_rand() / mt_getrandmax()) <= $sampleRate) {
                    $sampledData[$stat] = "$value|@$sampleRate";
                }
            }
        } else {
            $sampledData = $data;
        }

        if (empty($sampledData)) {
            return false;
        }

        // Wrap this in a try/catch - failures in any of this should be silently ignored
        try {
            $host = self::$config["host"];
            $port = self::$config["port"];

            $fp = fsockopen("udp://$host", $port, $errno, $errstr);
            if (!$fp) {
                /**
                 * @desc This is unlikely to happen, since UDP is connectionless.
                 */
                trigger_error("No statsd @ {$host}:{$port}: {$errstr} ({$errno})");
                return false;
            }
            foreach ($sampledData as $stat => $value) {
                $m      = "{$stat}:{$value}";
                $status = fwrite($fp, $m);
                if ($status === false) {
                    /**
                     * @desc This is unlikely to show you anything either.
                     */
                    trigger_error("Could not write to stream: {$m}");
                }
            }
            fclose($fp);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
}
