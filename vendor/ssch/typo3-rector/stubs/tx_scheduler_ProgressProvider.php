<?php

namespace RectorPrefix20211006;

if (\class_exists('tx_scheduler_ProgressProvider')) {
    return;
}
class tx_scheduler_ProgressProvider
{
}
\class_alias('tx_scheduler_ProgressProvider', 'tx_scheduler_ProgressProvider', \false);
