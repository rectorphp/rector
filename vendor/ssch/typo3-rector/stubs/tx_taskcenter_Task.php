<?php

namespace RectorPrefix20210827;

if (\class_exists('tx_taskcenter_Task')) {
    return;
}
class tx_taskcenter_Task
{
}
\class_alias('tx_taskcenter_Task', 'tx_taskcenter_Task', \false);
