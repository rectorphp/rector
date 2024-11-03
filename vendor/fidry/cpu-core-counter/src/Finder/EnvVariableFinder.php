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
namespace RectorPrefix202411\Fidry\CpuCoreCounter\Finder;

use function getenv;
use function preg_match;
use function sprintf;
use function var_export;
final class EnvVariableFinder implements CpuCoreFinder
{
    /** @var string */
    private $environmentVariableName;
    public function __construct(string $environmentVariableName)
    {
        $this->environmentVariableName = $environmentVariableName;
    }
    public function diagnose() : string
    {
        $value = getenv($this->environmentVariableName);
        return sprintf('parse(getenv(%s)=%s)=%s', $this->environmentVariableName, var_export($value, \true), self::isPositiveInteger($value) ? $value : 'null');
    }
    public function find() : ?int
    {
        $value = getenv($this->environmentVariableName);
        return self::isPositiveInteger($value) ? (int) $value : null;
    }
    public function toString() : string
    {
        return sprintf('getenv(%s)', $this->environmentVariableName);
    }
    /**
     * @param string|false $value
     */
    private static function isPositiveInteger($value) : bool
    {
        return \false !== $value && 1 === preg_match('/^\\d+$/', $value) && (int) $value > 0;
    }
}
