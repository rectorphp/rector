<?php

namespace RectorPrefix20210603;

if (\class_exists('t3lib_collection_AbstractRecordCollection')) {
    return;
}
class t3lib_collection_AbstractRecordCollection
{
}
\class_alias('t3lib_collection_AbstractRecordCollection', 't3lib_collection_AbstractRecordCollection', \false);
