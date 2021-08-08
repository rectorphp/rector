<?php

namespace RectorPrefix20210808;

if (\class_exists('t3lib_Compressor')) {
    return;
}
class t3lib_Compressor
{
}
\class_alias('t3lib_Compressor', 't3lib_Compressor', \false);
