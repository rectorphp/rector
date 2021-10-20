<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_error_http_AbstractClientErrorException')) {
    return;
}
class t3lib_error_http_AbstractClientErrorException
{
}
\class_alias('t3lib_error_http_AbstractClientErrorException', 't3lib_error_http_AbstractClientErrorException', \false);
