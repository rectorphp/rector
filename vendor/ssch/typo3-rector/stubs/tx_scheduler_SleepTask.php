<?php

namespace RectorPrefix20211020;

if (\class_exists('tx_scheduler_SleepTask')) {
    return;
}
class tx_scheduler_SleepTask
{
}
\class_alias('tx_scheduler_SleepTask', 'tx_scheduler_SleepTask', \false);
