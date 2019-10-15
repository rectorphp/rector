<?php

declare(strict_types=1);

namespace Rector\PHPUnit\ValueObject;

use PhpParser\Node\Arg;
use PHPStan\Type\Type;

final class DataProviderClassMethodRecipe
{
    /**
     * @var string
     */
    private $methodName;

    /**
     * @var Arg[]
     */
    private $args = [];

    /**
     * @var Type|null
     */
    private $type;

    /**
     * @param Arg[] $args
     */
    public function __construct(string $methodName, array $args, ?Type $type)
    {
        $this->methodName = $methodName;
        $this->args = $args;
        $this->type = $type;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    /**
     * @return Arg[]
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }
}
