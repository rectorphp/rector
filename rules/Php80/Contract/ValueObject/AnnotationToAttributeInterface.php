<?php

declare (strict_types=1);
namespace Rector\Php80\Contract\ValueObject;

interface AnnotationToAttributeInterface
{
    public function getTag() : string;
    public function getAttributeClass() : string;
}
