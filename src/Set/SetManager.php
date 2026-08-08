<?php

declare (strict_types=1);
namespace Rector\Set;

use Rector\Bridge\SetProviderCollector;
use Rector\Composer\InstalledPackageResolver;
use Rector\Set\Enum\SetGroup;
use Rector\Set\ValueObject\ComposerTriggeredSet;
/**
 * @deprecated Bond the rules themselves instead, by implementing the ComposerPackageConstraintInterface. A set
 * described as an object only existed to be matched against the installed packages; a bonded rule states the exact
 * package version its target API is available from and applies from there upwards, so a plain set file is enough.
 *
 * @see \Rector\VersionBonding\Contract\ComposerPackageConstraintInterface
 * @see https://github.com/rectorphp/rector-src/pull/8296
 */
final class SetManager
{
    /**
     * @readonly
     */
    private SetProviderCollector $setProviderCollector;
    /**
     * @readonly
     */
    private InstalledPackageResolver $installedPackageResolver;
    public function __construct(SetProviderCollector $setProviderCollector, InstalledPackageResolver $installedPackageResolver)
    {
        $this->setProviderCollector = $setProviderCollector;
        $this->installedPackageResolver = $installedPackageResolver;
    }
    /**
     * @param SetGroup::*[] $setGroups
     * @return string[]
     */
    public function matchBySetGroups(array $setGroups): array
    {
        $installedComposerPackages = $this->installedPackageResolver->resolve();
        $groupLoadedSets = [];
        foreach ($setGroups as $setGroup) {
            $composerTriggeredSets = $this->matchComposerTriggered($setGroup);
            foreach ($composerTriggeredSets as $composerTriggeredSet) {
                if ($composerTriggeredSet->matchInstalledPackages($installedComposerPackages)) {
                    // it matched composer package + version requirements → load set
                    $groupLoadedSets[] = realpath($composerTriggeredSet->getSetFilePath());
                }
            }
        }
        return $groupLoadedSets;
    }
    /**
     * @return ComposerTriggeredSet[]
     */
    private function matchComposerTriggered(string $groupName): array
    {
        $matchedSets = [];
        foreach ($this->setProviderCollector->provideComposerTriggeredSets() as $composerTriggeredSet) {
            if ($composerTriggeredSet->getGroupName() === $groupName) {
                $matchedSets[] = $composerTriggeredSet;
            }
        }
        return $matchedSets;
    }
}
