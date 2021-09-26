<?php

namespace RectorPrefix20210926;

if (\class_exists('t3lib_http_observer_Download')) {
    return;
}
class t3lib_http_observer_Download
{
}
\class_alias('t3lib_http_observer_Download', 't3lib_http_observer_Download', \false);
