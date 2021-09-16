<?php

namespace RectorPrefix20210916;

if (\class_exists('t3lib_TCEmain_checkModifyAccessListHook')) {
    return;
}
class t3lib_TCEmain_checkModifyAccessListHook
{
}
\class_alias('t3lib_TCEmain_checkModifyAccessListHook', 't3lib_TCEmain_checkModifyAccessListHook', \false);
