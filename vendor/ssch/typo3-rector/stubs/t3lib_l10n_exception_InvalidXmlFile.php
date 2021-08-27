<?php

namespace RectorPrefix20210827;

if (\class_exists('t3lib_l10n_exception_InvalidXmlFile')) {
    return;
}
class t3lib_l10n_exception_InvalidXmlFile
{
}
\class_alias('t3lib_l10n_exception_InvalidXmlFile', 't3lib_l10n_exception_InvalidXmlFile', \false);
