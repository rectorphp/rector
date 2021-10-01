<?php

namespace RectorPrefix20211001;

if (\class_exists('tx_saltedpasswords_abstract_salts')) {
    return;
}
class tx_saltedpasswords_abstract_salts
{
}
\class_alias('tx_saltedpasswords_abstract_salts', 'tx_saltedpasswords_abstract_salts', \false);
