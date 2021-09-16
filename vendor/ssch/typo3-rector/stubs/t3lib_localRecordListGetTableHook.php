<?php

namespace RectorPrefix20210916;

if (\class_exists('t3lib_localRecordListGetTableHook')) {
    return;
}
class t3lib_localRecordListGetTableHook
{
}
\class_alias('t3lib_localRecordListGetTableHook', 't3lib_localRecordListGetTableHook', \false);
