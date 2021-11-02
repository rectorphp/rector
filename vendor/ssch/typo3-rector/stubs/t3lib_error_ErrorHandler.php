<?php

namespace RectorPrefix20211102;

if (\class_exists('t3lib_error_ErrorHandler')) {
    return;
}
class t3lib_error_ErrorHandler
{
}
\class_alias('t3lib_error_ErrorHandler', 't3lib_error_ErrorHandler', \false);
