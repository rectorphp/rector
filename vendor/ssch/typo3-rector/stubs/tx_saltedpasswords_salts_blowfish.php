<?php

namespace RectorPrefix20210909;

if (\class_exists('tx_saltedpasswords_salts_blowfish')) {
    return;
}
class tx_saltedpasswords_salts_blowfish
{
}
\class_alias('tx_saltedpasswords_salts_blowfish', 'tx_saltedpasswords_salts_blowfish', \false);
