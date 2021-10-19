<?php

namespace RectorPrefix20211019;

if (\class_exists('tx_lowlevel_syslog')) {
    return;
}
class tx_lowlevel_syslog
{
}
\class_alias('tx_lowlevel_syslog', 'tx_lowlevel_syslog', \false);
