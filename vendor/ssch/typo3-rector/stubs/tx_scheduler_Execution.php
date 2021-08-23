<?php

namespace RectorPrefix20210823;

if (\class_exists('tx_scheduler_Execution')) {
    return;
}
class tx_scheduler_Execution
{
}
\class_alias('tx_scheduler_Execution', 'tx_scheduler_Execution', \false);
