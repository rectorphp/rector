<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer\Command;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Symfony\Enum\SymfonyAnnotation;
final class AttributeValueResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return string|\PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr\Array_|null
     */
    public function getArgumentValueFromAttribute(Class_ $class, int $argumentIndexKey)
    {
        foreach ($class->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attribute) {
                if (!$this->nodeNameResolver->isName($attribute->name, SymfonyAnnotation::AS_COMMAND)) {
                    continue;
                }
                if (!isset($attribute->args[$argumentIndexKey])) {
                    continue;
                }
                $arg = $attribute->args[$argumentIndexKey];
                if ($arg->value instanceof String_) {
                    return $arg->value->value;
                } elseif ($arg->value instanceof ConstFetch || $arg->value instanceof Array_) {
                    return $arg->value;
                }
            }
        }
        return null;
    }
}
