<?php

namespace RectorPrefix20210725;

if (\class_exists('t3lib_cacheHash')) {
    return;
}
class t3lib_cacheHash
{
}
\class_alias('t3lib_cacheHash', 't3lib_cacheHash', \false);
