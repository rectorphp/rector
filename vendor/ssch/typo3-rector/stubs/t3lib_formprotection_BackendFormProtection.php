<?php

namespace RectorPrefix20210916;

if (\class_exists('t3lib_formprotection_BackendFormProtection')) {
    return;
}
class t3lib_formprotection_BackendFormProtection
{
}
\class_alias('t3lib_formprotection_BackendFormProtection', 't3lib_formprotection_BackendFormProtection', \false);
