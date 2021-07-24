<?php

namespace RectorPrefix20210724;

if (\class_exists('SC_logout')) {
    return;
}
class SC_logout
{
}
\class_alias('SC_logout', 'SC_logout', \false);
