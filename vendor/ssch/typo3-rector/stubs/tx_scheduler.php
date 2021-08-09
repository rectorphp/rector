<?php

namespace RectorPrefix20210809;

if (\class_exists('tx_scheduler')) {
    return;
}
class tx_scheduler
{
}
\class_alias('tx_scheduler', 'tx_scheduler', \false);
