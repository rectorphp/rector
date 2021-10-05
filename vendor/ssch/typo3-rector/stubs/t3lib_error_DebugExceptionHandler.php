<?php

namespace RectorPrefix20211005;

if (\class_exists('t3lib_error_DebugExceptionHandler')) {
    return;
}
class t3lib_error_DebugExceptionHandler
{
}
\class_alias('t3lib_error_DebugExceptionHandler', 't3lib_error_DebugExceptionHandler', \false);
