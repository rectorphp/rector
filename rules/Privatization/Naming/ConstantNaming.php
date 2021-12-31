<?php

declare (strict_types=1);
namespace Rector\Privatization\Naming;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\NodeNameResolver\NodeNameResolver;
use RectorPrefix20211231\Symfony\Component\String\UnicodeString;
final class ConstantNaming
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param \PhpParser\Node\Expr\Variable|\PhpParser\Node\Stmt\PropertyProperty $propertyProperty
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
    public function createFromVariable(\PhpParser\Node\Expr\Variable $variable)
    {
        $variableName = $this->nodeNameResolver->getName($variable);
        if ($variableName === null) {
            return null;
        }
        return $this->createUnderscoreUppercaseString($variableName);
    }
    private function createUnderscoreUppercaseString(string $propertyName) : string
    {
        $propertyNameUnicodeString = new \RectorPrefix20211231\Symfony\Component\String\UnicodeString($propertyName);
        return $propertyNameUnicodeString->snake()->upper()->toString();
    }
}
