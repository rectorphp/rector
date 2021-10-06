<?php

namespace RectorPrefix20211006;

if (\class_exists('t3lib_PdoHelper')) {
    return;
}
class t3lib_PdoHelper
{
}
\class_alias('t3lib_PdoHelper', 't3lib_PdoHelper', \false);
