<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class NameTypeResolver implements PerNodeTypeResolverInterface
{
    public function getNodeClass(): string
    {
        return Name::class;
    }

    /**
     * @param Name $nameNode
     */
    public function resolve(Node $nameNode): ?string
    {
        /** @var FullyQualified $fqnName */
        $fqnName = $nameNode->getAttribute(Attribute::RESOLVED_NAME);

        return $fqnName->toString();
    }
}
