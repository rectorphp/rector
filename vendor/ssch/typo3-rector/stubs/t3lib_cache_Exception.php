<?php

namespace RectorPrefix20210909;

if (\class_exists('t3lib_cache_Exception')) {
    return;
}
class t3lib_cache_Exception
{
}
\class_alias('t3lib_cache_Exception', 't3lib_cache_Exception', \false);
