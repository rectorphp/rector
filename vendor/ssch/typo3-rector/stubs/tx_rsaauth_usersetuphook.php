<?php

namespace RectorPrefix20210909;

if (\class_exists('tx_rsaauth_usersetuphook')) {
    return;
}
class tx_rsaauth_usersetuphook
{
}
\class_alias('tx_rsaauth_usersetuphook', 'tx_rsaauth_usersetuphook', \false);
