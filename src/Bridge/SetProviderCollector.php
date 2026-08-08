<?php

declare (strict_types=1);
namespace Rector\Bridge;

use Rector\Set\Contract\SetInterface;
use Rector\Set\Contract\SetProviderInterface;
use Rector\Set\ValueObject\ComposerTriggeredSet;
/**
 * @api
 *
 * Utils class to ease building bridges by 3rd-party tools
 *
 * @deprecated Bond the rules themselves instead, by implementing the ComposerPackageConstraintInterface. A set
 * described as an object only existed to be matched against the installed packages; a bonded rule states the exact
 * package version its target API is available from and applies from there upwards, so a plain set file is enough.
 *
 * @see \Rector\VersionBonding\Contract\ComposerPackageConstraintInterface
 * @see https://github.com/rectorphp/rector-src/pull/8296
 */
final class SetProviderCollector
{
    /**
     * @var SetProviderInterface[]
     * @readonly
     */
    private array $setProviders = [];
    /**
     * @param SetProviderInterface[] $setProviders
     */
    public function __construct(array $setProviders = [])
    {
        $this->setProviders = $setProviders;
    }
    /**
     * @return array<SetProviderInterface>
     */
    public function provide(): array
    {
        return $this->setProviders;
    }
    /**
     * @return array<SetInterface>
     */
    public function provideSets(): array
    {
        $sets = [];
        foreach ($this->setProviders as $setProvider) {
            $sets = array_merge($sets, $setProvider->provide());
        }
        return $sets;
    }
    /**
     * @return array<ComposerTriggeredSet>
     */
    public function provideComposerTriggeredSets(): array
    {
        return array_filter($this->provideSets(), fn(SetInterface $set): bool => $set instanceof ComposerTriggeredSet);
    }
}
