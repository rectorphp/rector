<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_error_http_BadRequestException')) {
    return;
}
class t3lib_error_http_BadRequestException
{
}
\class_alias('t3lib_error_http_BadRequestException', 't3lib_error_http_BadRequestException', \false);
