<?php

namespace RectorPrefix20210902;

if (\class_exists('SC_file_rename')) {
    return;
}
class SC_file_rename
{
}
\class_alias('SC_file_rename', 'SC_file_rename', \false);
