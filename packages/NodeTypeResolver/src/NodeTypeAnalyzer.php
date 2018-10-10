<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use Countable;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Node\Attribute;
use function Safe\class_implements;

final class NodeTypeAnalyzer
{
    public function isStringType(Node $node): bool
    {
        if (! $node instanceof Expr) {
            return false;
        }

        $nodeType = $this->getNodeType($node);

        return $nodeType instanceof StringType;
    }

    public function isCountableType(Node $node): bool
    {
        if (! $node instanceof Expr) {
            return false;
        }

        $nodeType = $this->getNodeType($node);
        if ($nodeType instanceof ConstantArrayType) {
            return true;
        }

        if ($nodeType instanceof ObjectType) {
            $className = $nodeType->getClassName();
            if (in_array(Countable::class, class_implements($className), true)) {
                return true;
            }

            return false;
        }

        return false;
    }

    private function getNodeType(Expr $node): Type
    {
        /** @var Scope $nodeScope */
        $nodeScope = $node->getAttribute(Attribute::SCOPE);

        return $nodeScope->getType($node);
    }
}
