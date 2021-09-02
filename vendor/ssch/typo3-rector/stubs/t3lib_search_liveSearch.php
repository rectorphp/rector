<?php

namespace RectorPrefix20210902;

if (\class_exists('t3lib_search_liveSearch')) {
    return;
}
class t3lib_search_liveSearch
{
}
\class_alias('t3lib_search_liveSearch', 't3lib_search_liveSearch', \false);
