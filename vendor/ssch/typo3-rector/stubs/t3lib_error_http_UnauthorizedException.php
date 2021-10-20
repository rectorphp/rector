<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_error_http_UnauthorizedException')) {
    return;
}
class t3lib_error_http_UnauthorizedException
{
}
\class_alias('t3lib_error_http_UnauthorizedException', 't3lib_error_http_UnauthorizedException', \false);
