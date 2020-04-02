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
     * @var string
     */
    private $printedContent;

    /**
     * @var bool
     */
    private $hasRequiredArguments = false;

    /**
     * @var string[]
     */
    private $publicPropertyInfos = [];

    public function __construct(string $class, string $printedContent, bool $hasRequiredArguments)
    {
        $this->class = $class;
        $this->printedContent = $printedContent;
        $this->hasRequiredArguments = $hasRequiredArguments;

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

    public function getPrintedContent(): string
    {
        return $this->printedContent;
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
