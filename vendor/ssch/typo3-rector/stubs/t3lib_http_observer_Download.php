<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_http_observer_Download')) {
    return;
}
class t3lib_http_observer_Download
{
}
\class_alias('t3lib_http_observer_Download', 't3lib_http_observer_Download', \false);
