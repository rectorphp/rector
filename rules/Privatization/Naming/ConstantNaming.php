<?php

declare (strict_types=1);
namespace Rector\Privatization\Naming;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20220609\Symfony\Component\String\UnicodeString;
final class ConstantNaming
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
     * @param \PhpParser\Node\Stmt\PropertyProperty|\PhpParser\Node\Expr\Variable $propertyProperty
     */
    public function createFromProperty($propertyProperty) : string
    {
        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($propertyProperty);
        return $this->createUnderscoreUppercaseString($propertyName);
    }
    /**
     * @return string|null
     */
    public function createFromVariable(Variable $variable)
    {
        $variableName = $this->nodeNameResolver->getName($variable);
        if ($variableName === null) {
            return null;
        }
        return $this->createUnderscoreUppercaseString($variableName);
    }
    private function createUnderscoreUppercaseString(string $propertyName) : string
    {
        $propertyNameUnicodeString = new UnicodeString($propertyName);
        return $propertyNameUnicodeString->snake()->upper()->toString();
    }
}
