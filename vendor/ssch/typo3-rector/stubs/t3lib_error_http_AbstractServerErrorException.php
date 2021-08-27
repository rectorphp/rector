<?php

namespace RectorPrefix20210827;

if (\class_exists('t3lib_error_http_AbstractServerErrorException')) {
    return;
}
class t3lib_error_http_AbstractServerErrorException
{
}
\class_alias('t3lib_error_http_AbstractServerErrorException', 't3lib_error_http_AbstractServerErrorException', \false);
