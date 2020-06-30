<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Matcher;

use PhpParser\Node\Expr;
use PHPStan\Type\TypeWithClassName;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class ClassNameTypeMatcher
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @param string[] $names
     */
    public function doesExprMatchNames(Expr $expr, array $names): bool
    {
        $exprType = $this->nodeTypeResolver->getStaticType($expr);
        if (! $exprType instanceof TypeWithClassName) {
            return false;
        }

        $className = $exprType->getClassName();

        foreach ($names as $name) {
            if (is_a($className, $name, true)) {
                return true;
            }

            if (fnmatch($name, $className)) {
                return true;
            }
        }

        return false;
    }
}
