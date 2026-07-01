<?php

declare (strict_types=1);
namespace RectorPrefix202607;

use RectorPrefix202607\Boundwize\StructArmed\Architecture;
use RectorPrefix202607\Boundwize\StructArmed\Preset\Preset;
return Architecture::define()->withPresets(Preset::PSR1(), Preset::PSR4());
