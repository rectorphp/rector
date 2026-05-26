<?php

declare (strict_types=1);
namespace RectorPrefix202605;

use RectorPrefix202605\Boundwize\StructArmed\Architecture;
use RectorPrefix202605\Boundwize\StructArmed\Preset\Preset;
return Architecture::define()->withPresets(Preset::PSR1(), Preset::PSR4());
