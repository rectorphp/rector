<?php

declare (strict_types=1);
namespace Rector\Set;

use Rector\Composer\InstalledPackageResolver;
use Rector\Set\Contract\SetProviderInterface;
use Rector\Set\ValueObject\ComposerTriggeredSet;
use RectorPrefix202406\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Set\SetCollector\SetCollectorTest
 */
final class SetManager
{
    /**
     * @var SetProviderInterface[]
     * @readonly
     */
    private $setProviders;
    /**
     * @param SetProviderInterface[] $setProviders
     */
    public function __construct(array $setProviders)
    {
        $this->setProviders = $setProviders;
        Assert::allIsInstanceOf($setProviders, SetProviderInterface::class);
    }
    /**
     * @return ComposerTriggeredSet[]
     */
    public function matchComposerTriggered(string $groupName) : array
    {
        $matchedSets = [];
        foreach ($this->setProviders as $setProvider) {
            foreach ($setProvider->provide() as $set) {
                if (!$set instanceof ComposerTriggeredSet) {
                    continue;
                }
                if ($set->getGroupName() === $groupName) {
                    $matchedSets[] = $set;
                }
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
