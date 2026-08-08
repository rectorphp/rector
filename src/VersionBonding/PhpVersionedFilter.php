<?php

declare (strict_types=1);
namespace Rector\VersionBonding;

use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Contract\Rector\RectorInterface;
use Rector\Php\PhpVersionProvider;
use Rector\Php\PolyfillPackagesProvider;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Rector\VersionBonding\Contract\RelatedPolyfillInterface;
/**
 * @see \Rector\Tests\VersionBonding\PhpVersionedFilterTest
 */
final class PhpVersionedFilter
{
    /**
     * @readonly
     */
    private PhpVersionProvider $phpVersionProvider;
    /**
     * @readonly
     */
    private PolyfillPackagesProvider $polyfillPackagesProvider;
    public function __construct(PhpVersionProvider $phpVersionProvider, PolyfillPackagesProvider $polyfillPackagesProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
        $this->polyfillPackagesProvider = $polyfillPackagesProvider;
    }
    /**
     * @param list<RectorInterface> $rectors
     * @return list<RectorInterface>
     */
    public function filter(array $rectors): array
    {
        $minProjectPhpVersion = $this->phpVersionProvider->provide();
        $ceilingPhpVersion = $this->resolveCeilingPhpVersion();
        $activeRectors = [];
        foreach ($rectors as $rector) {
            // polyfill package can raise the rule above the project PHP version,
            // but never above an explicitly picked withPhpSets() version
            if ($rector instanceof RelatedPolyfillInterface && $ceilingPhpVersion === null) {
                $polyfillPackageNames = $this->polyfillPackagesProvider->provide();
                if (in_array($rector->providePolyfillPackage(), $polyfillPackageNames, \true)) {
                    $activeRectors[] = $rector;
                    continue;
                }
            }
            if (!$rector instanceof MinPhpVersionInterface) {
                $activeRectors[] = $rector;
                continue;
            }
            $maxPhpVersion = $rector instanceof RelatedPolyfillInterface && $ceilingPhpVersion !== null ? $ceilingPhpVersion : $minProjectPhpVersion;
            // does satisfy version? → include
            if ($rector->provideMinPhpVersion() <= $maxPhpVersion) {
                $activeRectors[] = $rector;
            }
        }
        return $activeRectors;
    }
    private function resolveCeilingPhpVersion(): ?int
    {
        if (!SimpleParameterProvider::hasParameter(Option::POLYFILL_CEILING_PHP_VERSION)) {
            return null;
        }
        $ceilingPhpVersion = SimpleParameterProvider::provideIntParameter(Option::POLYFILL_CEILING_PHP_VERSION);
        if ($ceilingPhpVersion <= 0) {
            return null;
        }
        return $ceilingPhpVersion;
    }
}
