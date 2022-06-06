<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Compatibility\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\NullableType;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\Value\ValueResolver;
final class RequiredAnnotationPropertyAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function isRequiredProperty(PhpDocInfo $phpDocInfo, Property $property) : bool
    {
        if ($phpDocInfo->hasByAnnotationClass('Doctrine\\Common\\Annotations\\Annotation\\Required')) {
            return \true;
        }
        // sometimes property has default null, but @var says its not null - that's due to nullability of typed properties
        // in that case, we should treat property as required
        $firstProperty = $property->props[0];
        if (!$firstProperty->default instanceof Expr) {
            return \false;
        }
        if (!$this->valueResolver->isNull($firstProperty->default)) {
            return \false;
        }
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return \false;
        }
        if ($varTagValueNode->type instanceof NullableTypeNode) {
            return \false;
        }
        return $property->type instanceof NullableType;
    }
}
