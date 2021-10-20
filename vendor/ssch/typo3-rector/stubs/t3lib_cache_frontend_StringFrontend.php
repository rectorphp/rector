<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_cache_frontend_StringFrontend')) {
    return;
}
class t3lib_cache_frontend_StringFrontend
{
}
\class_alias('t3lib_cache_frontend_StringFrontend', 't3lib_cache_frontend_StringFrontend', \false);
