<?php

declare (strict_types=1);
namespace Rector\Set;

use Rector\Bridge\SetProviderCollector;
use Rector\Composer\InstalledPackageResolver;
use Rector\Set\ValueObject\ComposerTriggeredSet;
/**
 * @see \Rector\Tests\Set\SetManager\SetManagerTest
 */
final class SetManager
{
    /**
     * @readonly
     * @var \Rector\Bridge\SetProviderCollector
     */
    private $setProviderCollector;
    public function __construct(SetProviderCollector $setProviderCollector)
    {
        $this->setProviderCollector = $setProviderCollector;
    }
    /**
     * @return ComposerTriggeredSet[]
     */
    public function matchComposerTriggered(string $groupName) : array
    {
        $matchedSets = [];
        foreach ($this->setProviderCollector->provideSets() as $set) {
            if (!$set instanceof ComposerTriggeredSet) {
                continue;
            }
            if ($set->getGroupName() === $groupName) {
                $matchedSets[] = $set;
            }
        }
        return $matchedSets;
    }
    /**
     * @param string[] $setGroups
     * @return string[]
     */
    public function matchBySetGroups(array $setGroups) : array
    {
        $installedPackageResolver = new InstalledPackageResolver();
        $installedComposerPackages = $installedPackageResolver->resolve(\getcwd());
        $groupLoadedSets = [];
        foreach ($setGroups as $setGroup) {
            $composerTriggeredSets = $this->matchComposerTriggered($setGroup);
            foreach ($composerTriggeredSets as $composerTriggeredSet) {
                if ($composerTriggeredSet->matchInstalledPackages($installedComposerPackages)) {
                    // @todo add debug note somewhere
                    // echo sprintf('Loaded "%s" set as it meets the conditions', $composerTriggeredSet->getSetFilePath());
                    // it matched composer package + version requirements → load set
                    $groupLoadedSets[] = $composerTriggeredSet->getSetFilePath();
                }
            }
        }
        return $groupLoadedSets;
    }
}
