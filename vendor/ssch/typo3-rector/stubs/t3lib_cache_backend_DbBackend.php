<?php

namespace RectorPrefix20210601;

if (\class_exists('t3lib_cache_backend_DbBackend')) {
    return;
}
class t3lib_cache_backend_DbBackend
{
}
\class_alias('t3lib_cache_backend_DbBackend', 't3lib_cache_backend_DbBackend', \false);
