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

    /**
     * @param Method $methodNode
     * @return string
     */
    public function resolve(Node $methodNode): string
    {
        $classMethodNode = $methodNode->getAttribute(Attribute::METHOD_NODE);

        return $methodNode->getAttribute(Attribute::CLASS_NAME) . '::' . $classMethodNode->name->name;
    }
}
