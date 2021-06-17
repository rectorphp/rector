<?php

namespace RectorPrefix20210617;

if (\class_exists('t3lib_error_AbstractExceptionHandler')) {
    return;
}
class t3lib_error_AbstractExceptionHandler
{
}
\class_alias('t3lib_error_AbstractExceptionHandler', 't3lib_error_AbstractExceptionHandler', \false);
