<?php

namespace RectorPrefix20210909;

if (\class_exists('Tx_Extbase_Persistence_Exception_UnsupportedRelation')) {
    return;
}
class Tx_Extbase_Persistence_Exception_UnsupportedRelation
{
}
\class_alias('Tx_Extbase_Persistence_Exception_UnsupportedRelation', 'Tx_Extbase_Persistence_Exception_UnsupportedRelation', \false);
