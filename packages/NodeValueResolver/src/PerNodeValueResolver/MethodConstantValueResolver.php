<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\PerNodeValueResolver;

use PhpParser\Node;
use PhpParser\Node\Scalar\MagicConst\Method;
use Rector\Node\Attribute;
use Rector\NodeValueResolver\Contract\PerNodeValueResolver\PerNodeValueResolverInterface;

final class MethodConstantValueResolver implements PerNodeValueResolverInterface
{
    public function getNodeClass(): string
    {
        return Method::class;
    }

    public function resolve(Node $arrayNode): string
    {
        $classMethodNode = $arrayNode->getAttribute(Attribute::SCOPE_NODE);

        return $arrayNode->getAttribute(Attribute::CLASS_NAME) . '::' . $classMethodNode->name->name;
    }
}
