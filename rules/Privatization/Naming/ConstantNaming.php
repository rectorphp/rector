<?php

declare(strict_types=1);

namespace Rector\Privatization\Naming;

use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\NodeNameResolver\NodeNameResolver;
use Symfony\Component\String\UnicodeString;

final class ConstantNaming
{
    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver
    ) {
    }

    public function createFromProperty(PropertyProperty|Variable $propertyProperty): string
    {
        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($propertyProperty);
        return $this->createUnderscoreUppercaseString($propertyName);
    }

    public function createFromVariable(Variable $variable): string|null
    {
        $variableName = $this->nodeNameResolver->getName($variable);
        if ($variableName === null) {
            return null;
        }

        return $this->createUnderscoreUppercaseString($variableName);
    }

    private function createUnderscoreUppercaseString(string $propertyName): string
    {
        $propertyNameUnicodeString = new UnicodeString($propertyName);
        return $propertyNameUnicodeString->snake()
            ->upper()
            ->toString();
    }
}
