<?php declare(strict_types=1);

namespace Rector\NodeFactory;

use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;

final class NodeFactory
{
    /**
     * Creates "$this->propertyName"
     */
    public function createLocalPropertyFetch(string $propertyName): PropertyFetch
    {
        return new PropertyFetch(
            new Variable('this', [
                'name' => $propertyName,
            ]),
            $propertyName
        );
    }
}
