<?php

namespace RectorPrefix20210909;

if (\interface_exists('t3lib_error_ErrorHandlerInterface')) {
    return;
}
interface t3lib_error_ErrorHandlerInterface
{
}
\class_alias('t3lib_error_ErrorHandlerInterface', 't3lib_error_ErrorHandlerInterface', \false);
