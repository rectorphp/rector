<?php declare(strict_types=1);

namespace Rector\NodeValueResolver\PerNodeValueResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use Rector\Node\Attribute;
use Rector\NodeValueResolver\Contract\PerNodeValueResolver\PerNodeValueResolverInterface;

final class ClassConstFetchResolver implements PerNodeValueResolverInterface
{
    public function getNodeClass(): string
    {
        return ClassConstFetch::class;
    }

    /**
     * @param ClassConstFetch $classConstFetchNode
     */
    public function resolve(Node $classConstFetchNode): string
    {
        $class = $classConstFetchNode->class->getAttribute(Attribute::RESOLVED_NAME)
            ->toString();

        $constant = $classConstFetchNode->name->toString();

        return $class . '::' . $constant;
    }
}
