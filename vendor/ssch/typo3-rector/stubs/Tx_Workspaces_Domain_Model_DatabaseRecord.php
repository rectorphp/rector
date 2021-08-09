<?php

namespace RectorPrefix20210809;

if (\class_exists('Tx_Workspaces_Domain_Model_DatabaseRecord')) {
    return;
}
class Tx_Workspaces_Domain_Model_DatabaseRecord
{
}
\class_alias('Tx_Workspaces_Domain_Model_DatabaseRecord', 'Tx_Workspaces_Domain_Model_DatabaseRecord', \false);
