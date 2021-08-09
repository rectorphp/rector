<?php

namespace RectorPrefix20210809;

if (\class_exists('t3lib_lock')) {
    return;
}
class t3lib_lock
{
}
\class_alias('t3lib_lock', 't3lib_lock', \false);
