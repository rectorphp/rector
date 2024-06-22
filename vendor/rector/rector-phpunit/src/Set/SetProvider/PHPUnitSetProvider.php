<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Set\SetProvider;

use Rector\Set\Contract\SetProviderInterface;
use Rector\Set\ValueObject\ComposerTriggeredSet;
/**
 * @api collected in core
 */
final class PHPUnitSetProvider implements SetProviderInterface
{
    /**
     * @return ComposerTriggeredSet[]
     */
    public function provide() : array
    {
        return [new ComposerTriggeredSet('phpunit', 'phpunit/phpunit', '4.0', __DIR__ . '/../../../config/sets/phpunit40.php'), new ComposerTriggeredSet('phpunit', 'phpunit/phpunit', '5.0', __DIR__ . '/../../../config/sets/phpunit50.php'), new ComposerTriggeredSet('phpunit', 'phpunit/phpunit', '6.0', __DIR__ . '/../../../config/sets/phpunit60.php'), new ComposerTriggeredSet('phpunit', 'phpunit/phpunit', '7.0', __DIR__ . '/../../../config/sets/phpunit70.php'), new ComposerTriggeredSet('phpunit', 'phpunit/phpunit', '8.0', __DIR__ . '/../../../config/sets/phpunit80.php'), new ComposerTriggeredSet('phpunit', 'phpunit/phpunit', '9.0', __DIR__ . '/../../../config/sets/phpunit90.php'), new ComposerTriggeredSet('phpunit', 'phpunit/phpunit', '10.0', __DIR__ . '/../../../config/sets/phpunit100.php'), new ComposerTriggeredSet('phpunit', 'phpunit/phpunit', '11.0', __DIR__ . '/../../../config/sets/phpunit110.php')];
    }
}
