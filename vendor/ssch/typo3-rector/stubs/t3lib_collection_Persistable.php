<?php

namespace RectorPrefix20210603;

if (\class_exists('t3lib_collection_Persistable')) {
    return;
}
class t3lib_collection_Persistable
{
}
\class_alias('t3lib_collection_Persistable', 't3lib_collection_Persistable', \false);
