<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Set\SetProvider;

use Rector\Set\Contract\SetInterface;
use Rector\Set\Contract\SetProviderInterface;
use Rector\Set\Enum\SetGroup;
use Rector\Set\ValueObject\ComposerTriggeredSet;
use Rector\Set\ValueObject\Set;
/**
 * @api collected in core
 */
final class PHPUnitSetProvider implements SetProviderInterface
{
    /**
     * @return SetInterface[]
     */
    public function provide() : array
    {
        return [new ComposerTriggeredSet(SetGroup::PHPUNIT, 'phpunit/phpunit', '4.0', __DIR__ . '/../../../config/sets/phpunit40.php'), new ComposerTriggeredSet(SetGroup::PHPUNIT, 'phpunit/phpunit', '5.0', __DIR__ . '/../../../config/sets/phpunit50.php'), new ComposerTriggeredSet(SetGroup::PHPUNIT, 'phpunit/phpunit', '6.0', __DIR__ . '/../../../config/sets/phpunit60.php'), new ComposerTriggeredSet(SetGroup::PHPUNIT, 'phpunit/phpunit', '7.0', __DIR__ . '/../../../config/sets/phpunit70.php'), new ComposerTriggeredSet(SetGroup::PHPUNIT, 'phpunit/phpunit', '8.0', __DIR__ . '/../../../config/sets/phpunit80.php'), new ComposerTriggeredSet(SetGroup::PHPUNIT, 'phpunit/phpunit', '9.0', __DIR__ . '/../../../config/sets/phpunit90.php'), new ComposerTriggeredSet(SetGroup::PHPUNIT, 'phpunit/phpunit', '10.0', __DIR__ . '/../../../config/sets/phpunit100.php'), new ComposerTriggeredSet(SetGroup::PHPUNIT, 'phpunit/phpunit', '11.0', __DIR__ . '/../../../config/sets/phpunit110.php'), new Set(SetGroup::PHPUNIT, 'Code Quality', __DIR__ . '/../../../config/sets/phpunit-code-quality.php'), new Set(SetGroup::ATTRIBUTES, 'PHPUnit Attributes', __DIR__ . '/../../../config/sets/annotations-to-attributes.php')];
    }
}
