<?php

namespace RectorPrefix20210909;

if (\class_exists('t3lib_TCEforms_Suggest_DefaultReceiver')) {
    return;
}
class t3lib_TCEforms_Suggest_DefaultReceiver
{
}
\class_alias('t3lib_TCEforms_Suggest_DefaultReceiver', 't3lib_TCEforms_Suggest_DefaultReceiver', \false);
