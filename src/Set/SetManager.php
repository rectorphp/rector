<?php

declare (strict_types=1);
namespace Rector\Set;

use Rector\Bridge\SetProviderCollector;
use Rector\Composer\InstalledPackageResolver;
use Rector\Set\Enum\SetGroup;
use Rector\Set\ValueObject\ComposerTriggeredSet;
/**
 * @see \Rector\Tests\Set\SetManager\SetManagerTest
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
     * @return ComposerTriggeredSet[]
     */
    public function matchComposerTriggered(string $groupName) : array
    {
        $matchedSets = [];
        foreach ($this->setProviderCollector->provideComposerTriggeredSets() as $composerTriggeredSet) {
            if ($composerTriggeredSet->getGroupName() === $groupName) {
                $matchedSets[] = $composerTriggeredSet;
            }
        }
        return $matchedSets;
    }
    /**
     * @param SetGroup::*[] $setGroups
     * @return string[]
     */
    public function matchBySetGroups(array $setGroups) : array
    {
        $installedComposerPackages = $this->installedPackageResolver->resolve();
        $groupLoadedSets = [];
        foreach ($setGroups as $setGroup) {
            $composerTriggeredSets = $this->matchComposerTriggered($setGroup);
            foreach ($composerTriggeredSets as $composerTriggeredSet) {
                if ($composerTriggeredSet->matchInstalledPackages($installedComposerPackages)) {
                    // it matched composer package + version requirements → load set
                    $groupLoadedSets[] = \realpath($composerTriggeredSet->getSetFilePath());
                }
            }
        }
        return $groupLoadedSets;
    }
}
