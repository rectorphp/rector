<?php

namespace RectorPrefix20210909;

if (\class_exists('tx_saltedpasswords_salts_phpass')) {
    return;
}
class tx_saltedpasswords_salts_phpass
{
}
\class_alias('tx_saltedpasswords_salts_phpass', 'tx_saltedpasswords_salts_phpass', \false);
