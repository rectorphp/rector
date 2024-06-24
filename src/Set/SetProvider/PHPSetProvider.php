<?php

declare (strict_types=1);
namespace Rector\Set\SetProvider;

use Rector\Set\Contract\SetInterface;
use Rector\Set\Contract\SetProviderInterface;
use Rector\Set\Enum\SetGroup;
use Rector\Set\ValueObject\Set;
final class PHPSetProvider implements SetProviderInterface
{
    /**
     * @return SetInterface[]
     */
    public function provide() : array
    {
        return [new Set(SetGroup::PHP, 'PHP 5.3', __DIR__ . '/../../../config/set/php53.php'), new Set(SetGroup::PHP, 'PHP 5.4', __DIR__ . '/../../../config/set/php54.php'), new Set(SetGroup::PHP, 'PHP 5.5', __DIR__ . '/../../../config/set/php55.php'), new Set(SetGroup::PHP, 'PHP 5.6', __DIR__ . '/../../../config/set/php56.php'), new Set(SetGroup::PHP, 'PHP 7.0', __DIR__ . '/../../../config/set/php70.php'), new Set(SetGroup::PHP, 'PHP 7.1', __DIR__ . '/../../../config/set/php71.php'), new Set(SetGroup::PHP, 'PHP 7.2', __DIR__ . '/../../../config/set/php72.php'), new Set(SetGroup::PHP, 'PHP 7.3', __DIR__ . '/../../../config/set/php73.php'), new Set(SetGroup::PHP, 'PHP 7.4', __DIR__ . '/../../../config/set/php74.php'), new Set(SetGroup::PHP, 'PHP 8.0', __DIR__ . '/../../../config/set/php80.php'), new Set(SetGroup::PHP, 'PHP 8.1', __DIR__ . '/../../../config/set/php81.php'), new Set(SetGroup::PHP, 'PHP 8.2', __DIR__ . '/../../../config/set/php82.php'), new Set(SetGroup::PHP, 'PHP 8.3', __DIR__ . '/../../../config/set/php83.php'), new Set(SetGroup::PHP, 'PHP 8.4', __DIR__ . '/../../../config/set/php84.php'), new Set(SetGroup::PHP, 'Polyfills', __DIR__ . '/../../../config/set/php-polyfills.php')];
    }
}
