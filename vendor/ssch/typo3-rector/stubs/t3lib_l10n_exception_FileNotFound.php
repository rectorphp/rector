<?php

namespace RectorPrefix20211115;

if (\class_exists('t3lib_l10n_exception_FileNotFound')) {
    return;
}
class t3lib_l10n_exception_FileNotFound
{
}
\class_alias('t3lib_l10n_exception_FileNotFound', 't3lib_l10n_exception_FileNotFound', \false);
