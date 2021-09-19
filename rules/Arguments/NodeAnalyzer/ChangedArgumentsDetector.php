<?php

declare(strict_types=1);

namespace Rector\Arguments\NodeAnalyzer;

use PhpParser\Node\Param;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;

final class ChangedArgumentsDetector
{
    public function __construct(
        private ValueResolver $valueResolver,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    /**
     * @param mixed $value
     */
    public function isDefaultValueChanged(Param $param, $value): bool
    {
        if ($param->default === null) {
            return false;
        }

        return ! $this->valueResolver->isValue($param->default, $value);
    }

    public function isTypeChanged(Param $param, ?string $type): bool
    {
        if ($param->type === null) {
            return false;
        }

        if ($type === null) {
            return true;
        }

        return ! $this->nodeNameResolver->isName($param->type, $type);
    }
}
