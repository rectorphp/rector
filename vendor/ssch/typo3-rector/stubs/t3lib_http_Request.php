<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_http_Request')) {
    return;
}
class t3lib_http_Request
{
}
\class_alias('t3lib_http_Request', 't3lib_http_Request', \false);
