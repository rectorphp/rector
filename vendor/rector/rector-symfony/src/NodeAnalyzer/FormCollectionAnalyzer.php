<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpParser\Node\Value\ValueResolver;
final class FormCollectionAnalyzer
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(ValueResolver $valueResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->valueResolver = $valueResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function isCollectionType(MethodCall $methodCall) : bool
    {
        $typeValue = $methodCall->getArgs()[1]->value;
        if (!$typeValue instanceof ClassConstFetch) {
            return $this->valueResolver->isValue($typeValue, 'collection');
        }
        if (!$this->nodeNameResolver->isName($typeValue->class, 'Symfony\\Component\\Form\\Extension\\Core\\Type\\CollectionType')) {
            return $this->valueResolver->isValue($typeValue, 'collection');
        }
        return \true;
    }
}
