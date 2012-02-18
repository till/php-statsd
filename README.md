## php-statsd

A client library for statsd. Based on etsy's work (of course).

### Installation

    pear channel-discover easybib.github.com/pear
    pear install easybib/StatsD-alpha


### Usage

    <?php
    require_once 'StatsD.php';

    // init this
    StatsD::init(array('enabled' => true, 'host' => 'statsd1'));

    // increment 'some-value'
    StatsD::increment('some-value');

    // increment multiple counters
    StatsD::increment(array('multiple', 'counters', 'work', 'as', 'well'));
