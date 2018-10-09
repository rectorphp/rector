<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Type\StringType;
use Rector\NodeTypeResolver\Node\Attribute;

final class NodeTypeAnalyzer
{
    public function isStringType(Node $node): bool
    {
        if (! $node instanceof Expr) {
            return false;
        }

        /** @var Scope $nodeScope */
        $nodeScope = $node->getAttribute(Attribute::SCOPE);

        $nodeType = $nodeScope->getType($node);

        return $nodeType instanceof StringType;
    }
}
