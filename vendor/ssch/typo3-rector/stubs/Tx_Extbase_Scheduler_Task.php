<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_Scheduler_Task')) {
    return;
}
class Tx_Extbase_Scheduler_Task
{
}
\class_alias('Tx_Extbase_Scheduler_Task', 'Tx_Extbase_Scheduler_Task', \false);
