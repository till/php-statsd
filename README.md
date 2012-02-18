## php-statsd

A client library for statsd. Based on etsy's work (of course).

### Installation

    pear channel-discover easybib.github.com/pear
    pear install easybib/StatsD-alpha


### Usage

The following is a basic PHP example:

    <?php
    require_once 'StatsD.php';

    // init this: enable logging, host is 'statsd1', default port is assumed
    StatsD::init(array('enabled' => true, 'host' => 'statsd1'));

    // increment 'some-value'
    StatsD::increment('some-value');

    // increment multiple counters
    StatsD::increment(array('multiple', 'counters', 'work', 'as', 'well'));


You will also need a statsd server, I'm currently using this fork: http://github.com/easybib/statsd

### Problem?

Sometimes things don't work. What I noticed is, that because UDP is connectionless,
it's extremely hard to debug potential connectivity issues from PHP. A TCP/IP based
connection would fail and error accordingly.

In case this code does not work as expected, it's most likely that either host or port
are wrong or that UDP is filtered between your instances (EC2-hint: security groups).
