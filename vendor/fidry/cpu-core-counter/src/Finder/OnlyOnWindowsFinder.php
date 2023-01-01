<?php

/*
 * This file is part of the Fidry CPUCounter Config package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace RectorPrefix202301\Fidry\CpuCoreCounter\Finder;

use function defined;
use function sprintf;
final class OnlyOnWindowsFinder implements CpuCoreFinder
{
    /**
     * @var CpuCoreFinder
     */
    private $decoratedFinder;
    public function __construct(CpuCoreFinder $decoratedFinder)
    {
        $this->decoratedFinder = $decoratedFinder;
    }
    public function diagnose() : string
    {
        return self::skip() ? 'Non-windows platform detected (PHP_WINDOWS_VERSION_MAJOR is not set).' : $this->decoratedFinder->diagnose();
    }
    public function find() : ?int
    {
        return self::skip() ? null : $this->decoratedFinder->find();
    }
    public function toString() : string
    {
        return sprintf('OnlyOnWindowsFinder(%s)', $this->decoratedFinder->toString());
    }
    private static function skip() : bool
    {
        // Skip if not on Windows. Rely on PHP to detect the platform
        // rather than reading the platform name or others.
        return !defined('PHP_WINDOWS_VERSION_MAJOR');
    }
}
