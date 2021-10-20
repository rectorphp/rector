<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_cache_backend_RedisBackend')) {
    return;
}
class t3lib_cache_backend_RedisBackend
{
}
\class_alias('t3lib_cache_backend_RedisBackend', 't3lib_cache_backend_RedisBackend', \false);
