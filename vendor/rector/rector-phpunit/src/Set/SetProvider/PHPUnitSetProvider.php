<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Set\SetProvider;

use Rector\Set\Contract\SetInterface;
use Rector\Set\Contract\SetProviderInterface;
use Rector\Set\Enum\SetGroup;
use Rector\Set\ValueObject\Set;
/**
 * @api collected in core
 */
final class PHPUnitSetProvider implements SetProviderInterface
{
    /**
     * @return SetInterface[]
     */
    public function provide(): array
    {
        return [
            // holds every rule bound to the exact PHPUnit version it is available from,
            // so the whole set can be run at once, no matter the PHPUnit version in the project
            new Set(SetGroup::PHPUNIT, 'Composer Based', __DIR__ . '/../../../config/sets/composer-based.php'),
            new Set(SetGroup::PHPUNIT, 'Code Quality', __DIR__ . '/../../../config/sets/phpunit-code-quality.php'),
            new Set(SetGroup::ATTRIBUTES, 'PHPUnit Attributes', __DIR__ . '/../../../config/sets/annotations-to-attributes.php'),
        ];
    }
}
