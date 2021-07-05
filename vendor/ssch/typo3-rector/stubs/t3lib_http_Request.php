<?php

namespace RectorPrefix20210705;

if (\class_exists('t3lib_http_Request')) {
    return;
}
class t3lib_http_Request
{
}
\class_alias('t3lib_http_Request', 't3lib_http_Request', \false);
