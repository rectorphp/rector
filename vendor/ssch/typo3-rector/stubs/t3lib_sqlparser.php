<?php

namespace RectorPrefix20210827;

if (\class_exists('t3lib_sqlparser')) {
    return;
}
class t3lib_sqlparser
{
}
\class_alias('t3lib_sqlparser', 't3lib_sqlparser', \false);
