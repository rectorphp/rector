<?php

declare (strict_types=1);
namespace Rector\Set\SetProvider;

use Rector\Set\Contract\SetInterface;
use Rector\Set\Contract\SetProviderInterface;
use Rector\Set\Enum\SetGroup;
use Rector\Set\ValueObject\ComposerTriggeredSet;
use Rector\Set\ValueObject\Set;
final class CoreSetProvider implements SetProviderInterface
{
    /**
     * @return SetInterface[]
     */
    public function provide() : array
    {
        return [new Set(SetGroup::CORE, 'Code Quality', __DIR__ . '/../../../config/set/code-quality.php'), new Set(SetGroup::CORE, 'Coding Style', __DIR__ . '/../../../config/set/coding-style.php'), new Set(SetGroup::CORE, 'Dead Code', __DIR__ . '/../../../config/set/dead-code.php'), new Set(SetGroup::CORE, 'DateTime to Carbon', __DIR__ . '/../../../config/set/datetime-to-carbon.php'), new Set(SetGroup::CORE, 'Instanceof', __DIR__ . '/../../../config/set/instanceof.php'), new Set(SetGroup::CORE, 'Early return', __DIR__ . '/../../../config/set/early-return.php'), new Set(SetGroup::CORE, 'Gmagick to Imagick', __DIR__ . '/../../../config/set/gmagick-to-imagick.php'), new Set(SetGroup::CORE, 'Naming', __DIR__ . '/../../../config/set/naming.php'), new Set(SetGroup::CORE, 'Privatization', __DIR__ . '/../../../config/set/privatization.php'), new Set(SetGroup::CORE, 'Strict Booleans', __DIR__ . '/../../../config/set/strict-booleans.php'), new Set(SetGroup::CORE, 'Type Declarations', __DIR__ . '/../../../config/set/type-declaration.php'), new ComposerTriggeredSet(SetGroup::NETTE_UTILS, 'nette/utils', '4.0', __DIR__ . '/../../../config/set/nette-utils/nette-utils4.php')];
    }
}
