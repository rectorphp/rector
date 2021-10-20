<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_formmail')) {
    return;
}
class t3lib_formmail
{
}
\class_alias('t3lib_formmail', 't3lib_formmail', \false);
