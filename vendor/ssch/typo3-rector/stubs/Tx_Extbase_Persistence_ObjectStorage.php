<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_Persistence_ObjectStorage')) {
    return;
}
class Tx_Extbase_Persistence_ObjectStorage
{
}
\class_alias('Tx_Extbase_Persistence_ObjectStorage', 'Tx_Extbase_Persistence_ObjectStorage', \false);
