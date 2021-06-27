<?php

namespace RectorPrefix20210627;

if (\class_exists('SC_file_upload')) {
    return;
}
class SC_file_upload
{
}
\class_alias('SC_file_upload', 'SC_file_upload', \false);
