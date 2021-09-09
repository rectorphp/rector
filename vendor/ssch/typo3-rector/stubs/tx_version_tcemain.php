<?php

namespace RectorPrefix20210909;

if (\class_exists('tx_version_tcemain')) {
    return;
}
class tx_version_tcemain
{
}
\class_alias('tx_version_tcemain', 'tx_version_tcemain', \false);
