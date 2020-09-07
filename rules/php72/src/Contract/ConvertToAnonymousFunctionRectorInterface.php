<?php

declare(strict_types=1);

namespace Rector\Php72\Contract;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\UnionType;

interface ConvertToAnonymousFunctionRectorInterface
{
    public function shouldSkip(Node $node): bool;

    /**
     * @return Param[]
     */
    public function getParameters(Node $node): array;

    /**
     * @return Identifier|Name|NullableType|UnionType|null
     */
    public function getReturnType(Node $node): ?Node;

    /**
     * @return Expression[]|Stmt[]
     */
    public function getBody(Node $node): array;
}
