<?php

namespace RectorPrefix20210916;

if (\class_exists('t3lib_formmail')) {
    return;
}
class t3lib_formmail
{
}
\class_alias('t3lib_formmail', 't3lib_formmail', \false);
