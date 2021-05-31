<?php

namespace RectorPrefix20210531;

if (\class_exists('t3lib_cache_Exception')) {
    return;
}
class t3lib_cache_Exception
{
}
\class_alias('t3lib_cache_Exception', 't3lib_cache_Exception', \false);
