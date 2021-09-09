<?php

namespace RectorPrefix20210909;

if (\class_exists('Tx_Extbase_Domain_Repository_FrontendUserRepository')) {
    return;
}
class Tx_Extbase_Domain_Repository_FrontendUserRepository
{
}
\class_alias('Tx_Extbase_Domain_Repository_FrontendUserRepository', 'Tx_Extbase_Domain_Repository_FrontendUserRepository', \false);
