<?php

declare(strict_types=1);

namespace Rector\Transform\ValueObject;

final class GetAndSetToMethodCall
{
    /**
     * @var class-string
     */
    private $classType;

    /**
     * @var string
     */
    private $getMethod;

    /**
     * @var string|null
     */
    private $setMethod;

    /**
     * @param class-string $classType
     */
    public function __construct(string $classType, string $getMethod, ?string $setMethod = null)
    {
        $this->classType = $classType;
        $this->getMethod = $getMethod;
        $this->setMethod = $setMethod;
    }

    /**
     * @return class-string
     */
    public function getClassType(): string
    {
        return $this->classType;
    }

    public function getGetMethod(): string
    {
        return $this->getMethod;
    }

    public function getSetMethod(): ?string
    {
        return $this->setMethod;
    }
}
