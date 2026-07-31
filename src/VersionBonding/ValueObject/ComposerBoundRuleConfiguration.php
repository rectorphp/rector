<?php

declare (strict_types=1);
namespace Rector\VersionBonding\ValueObject;

use Rector\Contract\Rector\ConfigurableRectorInterface;
/**
 * @see \Rector\Config\RectorConfig::ruleWithConfigurationComposerVersionBound()
 */
final class ComposerBoundRuleConfiguration
{
    /**
     * @var class-string<ConfigurableRectorInterface>
     * @readonly
     */
    private string $rectorClass;
    /**
     * @readonly
     */
    private string $packageName;
    /**
     * @readonly
     */
    private string $versionConstraint;
    /**
     * @var mixed[]
     * @readonly
     */
    private array $configuration;
    /**
     * @readonly
     */
    private bool $isActive;
    /**
     * @param class-string<ConfigurableRectorInterface> $rectorClass
     * @param mixed[] $configuration
     */
    public function __construct(string $rectorClass, string $packageName, string $versionConstraint, array $configuration, bool $isActive)
    {
        $this->rectorClass = $rectorClass;
        $this->packageName = $packageName;
        $this->versionConstraint = $versionConstraint;
        $this->configuration = $configuration;
        $this->isActive = $isActive;
    }
    /**
     * @return class-string<ConfigurableRectorInterface>
     */
    public function getRectorClass(): string
    {
        return $this->rectorClass;
    }
    public function getPackageName(): string
    {
        return $this->packageName;
    }
    public function getVersionConstraint(): string
    {
        return $this->versionConstraint;
    }
    /**
     * @return mixed[]
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }
    public function isActive(): bool
    {
        return $this->isActive;
    }
}
