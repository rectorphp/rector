<?php

namespace RectorPrefix20211011;

if (\class_exists('t3lib_cache_exception_InvalidData')) {
    return;
}
class t3lib_cache_exception_InvalidData
{
}
\class_alias('t3lib_cache_exception_InvalidData', 't3lib_cache_exception_InvalidData', \false);
