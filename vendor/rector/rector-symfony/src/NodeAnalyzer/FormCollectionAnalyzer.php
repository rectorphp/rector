<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\NodeAnalyzer;

use RectorPrefix20220606\PhpParser\Node\Expr\ClassConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\Value\ValueResolver;
use RectorPrefix20220606\Rector\NodeNameResolver\NodeNameResolver;
final class FormCollectionAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
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
