<?php

namespace RectorPrefix20210809;

if (\class_exists('SC_db_new')) {
    return;
}
class SC_db_new
{
}
\class_alias('SC_db_new', 'SC_db_new', \false);
