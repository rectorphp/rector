<?php

namespace RectorPrefix20210822;

if (\class_exists('t3lib_cache_backend_ApcBackend')) {
    return;
}
class t3lib_cache_backend_ApcBackend
{
}
\class_alias('t3lib_cache_backend_ApcBackend', 't3lib_cache_backend_ApcBackend', \false);
