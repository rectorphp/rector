<?php

namespace RectorPrefix20210909;

if (\class_exists('SC_file_rename')) {
    return;
}
class SC_file_rename
{
}
\class_alias('SC_file_rename', 'SC_file_rename', \false);
