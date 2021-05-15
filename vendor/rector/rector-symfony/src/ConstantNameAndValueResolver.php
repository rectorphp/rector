<?php

declare (strict_types=1);
namespace Rector\Symfony;

use PhpParser\Node\Attribute;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Symfony\ValueObject\ConstantNameAndValue;
final class ConstantNameAndValueResolver
{
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Symfony\ConstantNameAndValueMatcher
     */
    private $constantNameAndValueMatcher;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\Symfony\ConstantNameAndValueMatcher $constantNameAndValueMatcher)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->constantNameAndValueMatcher = $constantNameAndValueMatcher;
    }
    /**
     * @param Attribute[] $routeAttributes
     * @return ConstantNameAndValue[]
     */
    public function resolveFromAttributes(array $routeAttributes, string $prefixForNumeric) : array
    {
        $constantNameAndValues = [];
        foreach ($routeAttributes as $routeAttribute) {
            foreach ($routeAttribute->args as $arg) {
                if (!$this->nodeNameResolver->isName($arg, 'name')) {
                    continue;
                }
                $constantNameAndValue = $this->constantNameAndValueMatcher->matchFromArg($arg, $prefixForNumeric);
                if (!$constantNameAndValue instanceof \Rector\Symfony\ValueObject\ConstantNameAndValue) {
                    continue;
                }
                $constantNameAndValues[] = $constantNameAndValue;
            }
        }
        return $constantNameAndValues;
    }
}
