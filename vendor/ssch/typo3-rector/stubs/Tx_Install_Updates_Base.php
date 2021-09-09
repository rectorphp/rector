<?php

namespace RectorPrefix20210909;

if (\class_exists('Tx_Install_Updates_Base')) {
    return;
}
class Tx_Install_Updates_Base
{
}
\class_alias('Tx_Install_Updates_Base', 'Tx_Install_Updates_Base', \false);
