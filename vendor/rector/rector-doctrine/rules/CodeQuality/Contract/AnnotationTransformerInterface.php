<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Contract;

interface AnnotationTransformerInterface
{
    public function getClassName() : string;
}
