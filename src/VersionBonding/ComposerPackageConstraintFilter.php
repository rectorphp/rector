<?php

declare (strict_types=1);
namespace Rector\VersionBonding;

use RectorPrefix202602\Composer\Semver\Semver;
use Rector\Composer\InstalledPackageResolver;
use Rector\Contract\Rector\RectorInterface;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
/**
 * @see \Rector\Tests\VersionBonding\ComposerPackageConstraintFilterTest
 */
final class ComposerPackageConstraintFilter
{
    /**
     * @readonly
     */
    private InstalledPackageResolver $installedPackageResolver;
    public function __construct(InstalledPackageResolver $installedPackageResolver)
    {
        $this->installedPackageResolver = $installedPackageResolver;
    }
    /**
     * @param list<RectorInterface> $rectors
     * @return list<RectorInterface>
     */
    public function filter(array $rectors): array
    {
        $activeRectors = [];
        foreach ($rectors as $rector) {
            if (!$rector instanceof ComposerPackageConstraintInterface) {
                $activeRectors[] = $rector;
                continue;
            }
            if ($this->satisfiesComposerPackageConstraint($rector)) {
                $activeRectors[] = $rector;
            }
        }
        return $activeRectors;
    }
    private function satisfiesComposerPackageConstraint(ComposerPackageConstraintInterface $rector): bool
    {
        $composerPackageConstraint = $rector->provideComposerPackageConstraint();
        $packageVersion = $this->installedPackageResolver->resolvePackageVersion($composerPackageConstraint->getPackageName());
        if ($packageVersion === null) {
            return \false;
        }
        return Semver::satisfies($packageVersion, $composerPackageConstraint->getConstraint());
    }
}
