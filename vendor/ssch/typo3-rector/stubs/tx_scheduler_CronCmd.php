<?php

namespace RectorPrefix20210905;

if (\class_exists('tx_scheduler_CronCmd')) {
    return;
}
class tx_scheduler_CronCmd
{
}
\class_alias('tx_scheduler_CronCmd', 'tx_scheduler_CronCmd', \false);
