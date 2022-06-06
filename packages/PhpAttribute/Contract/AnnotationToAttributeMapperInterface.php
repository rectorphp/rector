<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PhpAttribute\Contract;

use RectorPrefix20220606\PhpParser\Node\Expr;
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
    public function map($value) : Expr;
}
