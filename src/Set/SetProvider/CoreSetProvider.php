<?php

declare (strict_types=1);
namespace Rector\Set\SetProvider;

use Rector\Set\Contract\SetInterface;
use Rector\Set\Contract\SetProviderInterface;
use Rector\Set\Enum\SetGroup;
use Rector\Set\ValueObject\Set;
/**
 * @deprecated Bond the rules themselves instead, by implementing the ComposerPackageConstraintInterface. A set
 * described as an object only existed to be matched against the installed packages; a bonded rule states the exact
 * package version its target API is available from and applies from there upwards, so a plain set file is enough.
 *
 * @see \Rector\VersionBonding\Contract\ComposerPackageConstraintInterface
 * @see https://github.com/rectorphp/rector-src/pull/8296
 */
final class CoreSetProvider implements SetProviderInterface
{
    /**
     * @return SetInterface[]
     */
    public function provide(): array
    {
        return [new Set(SetGroup::CORE, 'Code Quality', __DIR__ . '/../../../config/set/code-quality.php'), new Set(SetGroup::CORE, 'Coding Style', __DIR__ . '/../../../config/set/coding-style.php'), new Set(SetGroup::CORE, 'Dead Code', __DIR__ . '/../../../config/set/dead-code.php'), new Set(SetGroup::CORE, 'DateTime to Carbon', __DIR__ . '/../../../config/set/datetime-to-carbon.php'), new Set(SetGroup::CORE, 'Instanceof', __DIR__ . '/../../../config/set/instanceof.php'), new Set(SetGroup::CORE, 'Early return', __DIR__ . '/../../../config/set/early-return.php'), new Set(SetGroup::CORE, 'Gmagick to Imagick', __DIR__ . '/../../../config/set/gmagick-to-imagick.php'), new Set(SetGroup::CORE, 'Naming', __DIR__ . '/../../../config/set/naming.php'), new Set(SetGroup::CORE, 'Privatization', __DIR__ . '/../../../config/set/privatization.php'), new Set(SetGroup::CORE, 'Type Declarations', __DIR__ . '/../../../config/set/type-declaration.php')];
    }
}
