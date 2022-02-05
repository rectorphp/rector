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
     */
    public function map($value) : \PhpParser\Node\Expr;
}
