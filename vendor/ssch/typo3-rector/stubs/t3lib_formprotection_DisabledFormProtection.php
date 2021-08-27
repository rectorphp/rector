<?php

namespace RectorPrefix20210827;

if (\class_exists('t3lib_formprotection_DisabledFormProtection')) {
    return;
}
class t3lib_formprotection_DisabledFormProtection
{
}
\class_alias('t3lib_formprotection_DisabledFormProtection', 't3lib_formprotection_DisabledFormProtection', \false);
