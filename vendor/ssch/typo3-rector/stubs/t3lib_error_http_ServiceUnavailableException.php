<?php

namespace RectorPrefix20210827;

if (\class_exists('t3lib_error_http_ServiceUnavailableException')) {
    return;
}
class t3lib_error_http_ServiceUnavailableException
{
}
\class_alias('t3lib_error_http_ServiceUnavailableException', 't3lib_error_http_ServiceUnavailableException', \false);
