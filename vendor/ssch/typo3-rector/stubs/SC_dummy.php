<?php

namespace RectorPrefix20210720;

if (\class_exists('SC_dummy')) {
    return;
}
class SC_dummy
{
}
\class_alias('SC_dummy', 'SC_dummy', \false);
