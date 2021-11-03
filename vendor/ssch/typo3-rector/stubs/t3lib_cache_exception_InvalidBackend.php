<?php

namespace RectorPrefix20211103;

if (\class_exists('t3lib_cache_exception_InvalidBackend')) {
    return;
}
class t3lib_cache_exception_InvalidBackend
{
}
\class_alias('t3lib_cache_exception_InvalidBackend', 't3lib_cache_exception_InvalidBackend', \false);
