<?php

namespace RectorPrefix20210909;

if (\class_exists('tx_rsaauth_feloginhook')) {
    return;
}
class tx_rsaauth_feloginhook
{
}
\class_alias('tx_rsaauth_feloginhook', 'tx_rsaauth_feloginhook', \false);
