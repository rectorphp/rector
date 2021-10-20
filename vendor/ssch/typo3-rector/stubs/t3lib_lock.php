<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_lock')) {
    return;
}
class t3lib_lock
{
}
\class_alias('t3lib_lock', 't3lib_lock', \false);
