<?php

namespace RectorPrefix20210909;

if (\class_exists('tx_scheduler_ProgressProvider')) {
    return;
}
class tx_scheduler_ProgressProvider
{
}
\class_alias('tx_scheduler_ProgressProvider', 'tx_scheduler_ProgressProvider', \false);
