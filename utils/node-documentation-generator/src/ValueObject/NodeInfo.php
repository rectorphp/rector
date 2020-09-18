<?php

declare(strict_types=1);

namespace Rector\Utils\NodeDocumentationGenerator\ValueObject;

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
     * @var NodeCodeSample[]
     */
    private $nodeCodeSamples;

    /**
     * @param string[] $codeSamples
     * @param NodeCodeSample[] $nodeCodeSamples
     */
    public function __construct(
        string $class,
        array $codeSamples,
        bool $hasRequiredArguments,
        array $nodeCodeSamples = []
    ) {
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
        $this->nodeCodeSamples = $nodeCodeSamples;
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

    /**
     * @return NodeCodeSample[]
     */
    public function getNodeCodeSamples(): array
    {
        return $this->nodeCodeSamples;
    }
}
