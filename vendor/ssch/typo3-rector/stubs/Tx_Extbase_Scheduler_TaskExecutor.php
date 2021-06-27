<?php

namespace RectorPrefix20210627;

if (\class_exists('Tx_Extbase_Scheduler_TaskExecutor')) {
    return;
}
class Tx_Extbase_Scheduler_TaskExecutor
{
}
\class_alias('Tx_Extbase_Scheduler_TaskExecutor', 'Tx_Extbase_Scheduler_TaskExecutor', \false);
