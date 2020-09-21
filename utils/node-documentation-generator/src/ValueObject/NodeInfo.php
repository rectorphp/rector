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
     * @var string[]
     */
    private $publicPropertyInfos = [];

    /**
     * @var NodeCodeSample[]
     */
    private $nodeCodeSamples = [];

    /**
     * @var string
     */
    private $category;

    /**
     * @param NodeCodeSample[] $nodeCodeSamples
     */
    public function __construct(string $class, array $nodeCodeSamples = [], string $category)
    {
        $this->class = $class;

        $reflectionClass = new ReflectionClass($class);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            if ($reflectionProperty->name === 'attributes') {
                continue;
            }

            $this->publicPropertyInfos[] = ' * `$' . $reflectionProperty->name . '` - `' . $reflectionProperty->getDocComment() . '`';
        }
        $this->nodeCodeSamples = $nodeCodeSamples;
        $this->category = $category;
    }

    public function getClass(): string
    {
        return $this->class;
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

    public function getCategory(): string
    {
        return $this->category;
    }
}
