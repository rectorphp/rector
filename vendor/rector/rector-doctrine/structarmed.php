<?php

declare (strict_types=1);
namespace RectorPrefix202606;

use RectorPrefix202606\Boundwize\StructArmed\Architecture;
use RectorPrefix202606\Boundwize\StructArmed\Preset\Preset;
return Architecture::define()->withPresets(Preset::PSR1(), Preset::PSR4());
