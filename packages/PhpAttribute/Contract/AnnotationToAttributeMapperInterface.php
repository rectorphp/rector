<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\Contract;

use PhpParser\Node\Expr;
/**
 * @template T as mixed
 */
interface AnnotationToAttributeMapperInterface
{
    /**
     * @param mixed $value
     */
    public function isCandidate($value) : bool;
    /**
     * @param T $value
     * @return mixed[]|\PhpParser\Node\Expr
     */
    public function map($value);
}
