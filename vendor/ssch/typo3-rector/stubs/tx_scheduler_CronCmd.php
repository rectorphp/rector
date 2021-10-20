<?php

namespace RectorPrefix20211020;

if (\class_exists('tx_scheduler_CronCmd')) {
    return;
}
class tx_scheduler_CronCmd
{
}
\class_alias('tx_scheduler_CronCmd', 'tx_scheduler_CronCmd', \false);
