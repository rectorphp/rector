<?php

namespace RectorPrefix20210902;

if (\class_exists('Tx_Extbase_Persistence_QueryFactory')) {
    return;
}
class Tx_Extbase_Persistence_QueryFactory
{
}
\class_alias('Tx_Extbase_Persistence_QueryFactory', 'Tx_Extbase_Persistence_QueryFactory', \false);
