<?php

namespace RectorPrefix20210909;

if (\class_exists('t3lib_formprotection_Factory')) {
    return;
}
class t3lib_formprotection_Factory
{
}
\class_alias('t3lib_formprotection_Factory', 't3lib_formprotection_Factory', \false);
