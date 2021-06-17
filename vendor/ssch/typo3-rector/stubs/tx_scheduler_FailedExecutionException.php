<?php

namespace RectorPrefix20210617;

if (\class_exists('tx_scheduler_FailedExecutionException')) {
    return;
}
class tx_scheduler_FailedExecutionException
{
}
\class_alias('tx_scheduler_FailedExecutionException', 'tx_scheduler_FailedExecutionException', \false);
