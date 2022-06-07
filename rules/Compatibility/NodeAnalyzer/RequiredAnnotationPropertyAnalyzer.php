<?php

declare (strict_types=1);
namespace Rector\Compatibility\NodeAnalyzer;

use PhpParser\Node\Expr;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
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
