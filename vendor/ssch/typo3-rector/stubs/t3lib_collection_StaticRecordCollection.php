<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_collection_StaticRecordCollection')) {
    return;
}
class t3lib_collection_StaticRecordCollection
{
}
\class_alias('t3lib_collection_StaticRecordCollection', 't3lib_collection_StaticRecordCollection', \false);
