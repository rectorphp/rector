<?php

declare (strict_types=1);
namespace RectorPrefix202608\TomasVotruba\UnusedPublic;

use RectorPrefix202608\Webmozart\Assert\Assert;
/**
 * @inspired by https://github.com/phpstan/phpstan-symfony/blob/1.2.x/src/Symfony/Configuration.php
 */
final class Configuration
{
    /**
     * @var array<string, mixed>
     * @readonly
     */
    private array $parameters;
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }
    public function isUnusedMethodEnabled(): bool
    {
        $methods = $this->parameters['methods'] ?? \false;
        if (!is_bool($methods)) {
            return \false;
        }
        return $methods;
    }
    public function isUnusedRelativeMethodEnabled(): bool
    {
        $methods = $this->parameters['methods'] ?? \false;
        return is_numeric($methods);
    }
    /**
     * @return float|int
     */
    public function getMaximumRelativeUnusedPublicMethod()
    {
        return $this->parameters['methods'] ?? 0;
    }
    public function shouldCollectMethods(): bool
    {
        if ($this->parameters['methods']) {
            return \true;
        }
        return $this->parameters['local_methods'];
    }
    public function isLocalMethodEnabled(): bool
    {
        return $this->parameters['local_methods'] ?? \false;
    }
    public function isUnusedPropertyEnabled(): bool
    {
        return $this->parameters['properties'] ?? \false;
    }
    public function isUnusedConstantsEnabled(): bool
    {
        return $this->parameters['constants'] ?? \false;
    }
    /**
     * @return string[]
     */
    public function getTemplatePaths(): array
    {
        $templatePaths = $this->parameters['template_paths'];
        Assert::allDirectory($templatePaths);
        Assert::allFileExists($templatePaths);
        return $templatePaths;
    }
}
