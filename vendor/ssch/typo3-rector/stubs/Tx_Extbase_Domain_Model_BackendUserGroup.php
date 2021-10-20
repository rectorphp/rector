<?php

namespace RectorPrefix20211020;

if (\class_exists('Tx_Extbase_Domain_Model_BackendUserGroup')) {
    return;
}
class Tx_Extbase_Domain_Model_BackendUserGroup
{
}
\class_alias('Tx_Extbase_Domain_Model_BackendUserGroup', 'Tx_Extbase_Domain_Model_BackendUserGroup', \false);
