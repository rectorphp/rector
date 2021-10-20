<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_error_ProductionExceptionHandler')) {
    return;
}
class t3lib_error_ProductionExceptionHandler
{
}
\class_alias('t3lib_error_ProductionExceptionHandler', 't3lib_error_ProductionExceptionHandler', \false);
