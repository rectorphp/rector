<?php

namespace RectorPrefix20211018;

if (\class_exists('tx_scheduler_CachingFrameworkGarbageCollection')) {
    return;
}
class tx_scheduler_CachingFrameworkGarbageCollection
{
}
\class_alias('tx_scheduler_CachingFrameworkGarbageCollection', 'tx_scheduler_CachingFrameworkGarbageCollection', \false);
