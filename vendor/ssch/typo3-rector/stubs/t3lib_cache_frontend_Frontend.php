<?php

namespace RectorPrefix20210714;

if (\class_exists('t3lib_cache_frontend_Frontend')) {
    return;
}
class t3lib_cache_frontend_Frontend
{
}
\class_alias('t3lib_cache_frontend_Frontend', 't3lib_cache_frontend_Frontend', \false);
