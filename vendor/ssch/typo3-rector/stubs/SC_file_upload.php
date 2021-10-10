<?php

namespace RectorPrefix20211010;

if (\class_exists('SC_file_upload')) {
    return;
}
class SC_file_upload
{
}
\class_alias('SC_file_upload', 'SC_file_upload', \false);
