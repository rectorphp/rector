<?php

declare(strict_types=1);

namespace Rector\SymfonyPhpConfig\Tests\Functions\Source;

final class ServiceWithValueObject
{
    /**
     * @var WithType
     */
    private $withType;

    /**
     * @var WithType[]
     */
    private $withTypes = [];

    public function setWithType(WithType $withType): void
    {
        $this->withType = $withType;
    }

    public function getWithType(): WithType
    {
        return $this->withType;
    }

    /**
     * @param WithType[] $withTypes
     */
    public function setWithTypes(array $withTypes): void
    {
        $this->withTypes = $withTypes;
    }

    /**
     * @return WithType[]
     */
    public function getWithTypes(): array
    {
        return $this->withTypes;
    }
}

