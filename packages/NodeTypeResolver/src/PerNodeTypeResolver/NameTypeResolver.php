<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Name;
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
        /** @var Name|null $fqnName */
        $fqnName = $nameNode->getAttribute(Attribute::RESOLVED_NAME);
        if ($fqnName instanceof Name) {
            return $fqnName->toString();
        }

        return $nameNode->toString();
    }
}
