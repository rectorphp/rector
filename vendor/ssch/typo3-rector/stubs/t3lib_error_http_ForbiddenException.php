<?php

namespace RectorPrefix20210926;

if (\class_exists('t3lib_error_http_ForbiddenException')) {
    return;
}
class t3lib_error_http_ForbiddenException
{
}
\class_alias('t3lib_error_http_ForbiddenException', 't3lib_error_http_ForbiddenException', \false);
