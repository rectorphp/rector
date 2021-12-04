<?php

declare(strict_types=1);

namespace Rector\Core\ValueObject;

use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\String_;

final class SprintfStringAndArgs
{
    /**
     * @param Expr[] $arrayItems
     */
    public function __construct(
        private readonly String_ $string,
        private readonly array $arrayItems
    ) {
    }

    /**
     * @return Expr[]
     */
    public function getArrayItems(): array
    {
        return $this->arrayItems;
    }

    public function getStringValue(): string
    {
        return $this->string->value;
    }
}
