<?php declare(strict_types=1);

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
    private $providedType;

    /**
     * @param Arg[] $args
     */
    public function __construct(string $methodName, array $args, ?Type $providedType)
    {
        $this->methodName = $methodName;
        $this->args = $args;
        $this->providedType = $providedType;
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

    public function getProvidedType(): ?Type
    {
        return $this->providedType;
    }
}
