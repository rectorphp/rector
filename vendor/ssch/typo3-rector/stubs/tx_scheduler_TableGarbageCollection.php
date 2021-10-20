<?php

namespace RectorPrefix20211020;

if (\class_exists('tx_scheduler_TableGarbageCollection')) {
    return;
}
class tx_scheduler_TableGarbageCollection
{
}
\class_alias('tx_scheduler_TableGarbageCollection', 'tx_scheduler_TableGarbageCollection', \false);
