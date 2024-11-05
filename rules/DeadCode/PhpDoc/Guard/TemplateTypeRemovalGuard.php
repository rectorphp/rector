<?php

declare (strict_types=1);
namespace Rector\DeadCode\PhpDoc\Guard;

use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\Generic\TemplateType;
final class TemplateTypeRemovalGuard
{
    public function isLegal(Type $docType) : bool
    {
        // cover direct \PHPStan\Type\Generic\TemplateUnionType
        if ($docType instanceof TemplateType) {
            return \false;
        }
        // cover mixed template with mix from @template and non @template
        $types = $docType instanceof UnionType ? $docType->getTypes() : [$docType];
        foreach ($types as $type) {
            if ($type instanceof TemplateType) {
                return \false;
            }
        }
        return \true;
    }
}
