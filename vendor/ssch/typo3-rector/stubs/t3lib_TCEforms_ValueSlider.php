<?php

namespace RectorPrefix20211020;

if (\class_exists('t3lib_TCEforms_ValueSlider')) {
    return;
}
class t3lib_TCEforms_ValueSlider
{
}
\class_alias('t3lib_TCEforms_ValueSlider', 't3lib_TCEforms_ValueSlider', \false);
