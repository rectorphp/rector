<?php

namespace RectorPrefix20210602;

if (\class_exists('t3lib_cache_backend_NullBackend')) {
    return;
}
class t3lib_cache_backend_NullBackend
{
}
\class_alias('t3lib_cache_backend_NullBackend', 't3lib_cache_backend_NullBackend', \false);
