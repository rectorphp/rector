<?php

declare(strict_types=1);

namespace Rector\Utils\DocumentationGenerator\ValueObject;

use ReflectionClass;

final class NodeInfo
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var bool
     */
    private $hasRequiredArguments = false;

    /**
     * @var string[]
     */
    private $publicPropertyInfos = [];

    /**
     * @var string[]
     */
    private $codeSamples = [];

    /**
     * @param string[] $codeSamples
     */
    public function __construct(string $class, array $codeSamples, bool $hasRequiredArguments)
    {
        $this->class = $class;
        $this->hasRequiredArguments = $hasRequiredArguments;
        $this->codeSamples = $codeSamples;

        $reflectionClass = new ReflectionClass($class);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ($reflectionProperty->name === 'attributes') {
                continue;
            }

            $this->publicPropertyInfos[] = ' * `$' . $reflectionProperty->name . '` - `' . $reflectionProperty->getDocComment() . '`';
        }
    }

    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return string[]
     */
    public function getCodeSamples(): array
    {
        return $this->codeSamples;
    }

    public function hasRequiredArguments(): bool
    {
        return $this->hasRequiredArguments;
    }

    public function hasPublicProperties(): bool
    {
        return $this->publicPropertyInfos !== [];
    }

    /**
     * @return string[]
     */
    public function getPublicPropertyInfos(): array
    {
        return $this->publicPropertyInfos;
    }
}
