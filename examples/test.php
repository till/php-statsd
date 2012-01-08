<?php
require_once dirname(__FILE__) . '/../library/StatsD.php';

// port is omitted, it'll default to 8125
$config = array(
    // this is the hostname of your statsd daemon
    'host' => 'statsd1',

    // if it's enabled
    'enabled' => true
);

StatsD::init($config);

// increment the counters login, landingpage1 and pro
for ($x = 1; $x <= 100; ++$x) {
    echo "Counter {$x}" . PHP_EOL;
    var_dump(StatsD::increment(array('login', 'landingpage1', 'pro')));
}
