<?php

namespace RectorPrefix20211102;

if (\class_exists('t3lib_error_DebugExceptionHandler')) {
    return;
}
class t3lib_error_DebugExceptionHandler
{
}
\class_alias('t3lib_error_DebugExceptionHandler', 't3lib_error_DebugExceptionHandler', \false);
