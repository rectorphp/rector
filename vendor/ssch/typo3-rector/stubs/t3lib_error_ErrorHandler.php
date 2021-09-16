<?php

namespace RectorPrefix20210916;

if (\class_exists('t3lib_error_ErrorHandler')) {
    return;
}
class t3lib_error_ErrorHandler
{
}
\class_alias('t3lib_error_ErrorHandler', 't3lib_error_ErrorHandler', \false);
