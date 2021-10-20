<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_collection_RecordCollection')) {
    return;
}
class t3lib_collection_RecordCollection
{
}
\class_alias('t3lib_collection_RecordCollection', 't3lib_collection_RecordCollection', \false);
