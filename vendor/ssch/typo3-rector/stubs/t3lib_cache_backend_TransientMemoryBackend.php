<?php

namespace RectorPrefix20210909;

if (\class_exists('t3lib_cache_backend_TransientMemoryBackend')) {
    return;
}
class t3lib_cache_backend_TransientMemoryBackend
{
}
\class_alias('t3lib_cache_backend_TransientMemoryBackend', 't3lib_cache_backend_TransientMemoryBackend', \false);
