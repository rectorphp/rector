<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_error_http_StatusException')) {
    return;
}
class t3lib_error_http_StatusException
{
}
\class_alias('t3lib_error_http_StatusException', 't3lib_error_http_StatusException', \false);
