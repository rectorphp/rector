<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_formprotection_InvalidTokenException')) {
    return;
}
class t3lib_formprotection_InvalidTokenException
{
}
\class_alias('t3lib_formprotection_InvalidTokenException', 't3lib_formprotection_InvalidTokenException', \false);
