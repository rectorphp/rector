<?php

namespace RectorPrefix20210909;

if (\class_exists('t3lib_formprotection_InvalidTokenException')) {
    return;
}
class t3lib_formprotection_InvalidTokenException
{
}
\class_alias('t3lib_formprotection_InvalidTokenException', 't3lib_formprotection_InvalidTokenException', \false);
