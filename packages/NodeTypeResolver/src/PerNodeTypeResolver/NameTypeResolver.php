<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\MetadataAttribute;

final class NameTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Name::class, FullyQualified::class];
    }

    /**
     * @param Name $nameNode
     * @return string[]
     */
    public function resolve(Node $nameNode): array
    {
        if ($nameNode->toString() === 'parent') {
            return [$nameNode->getAttribute(MetadataAttribute::PARENT_CLASS_NAME)];
        }

        return [$this->resolveFullyQualifiedName($nameNode, $nameNode->toString())];
    }

    private function resolveFullyQualifiedName(Node $nameNode, string $name): string
    {
        if (in_array($name, ['self', 'static', 'this'], true)) {
            return $nameNode->getAttribute(MetadataAttribute::CLASS_NAME);
        }

        /** @var Name|null $name */
        $resolvedNameNode = $nameNode->getAttribute(Attribute::RESOLVED_NAME);
        if ($resolvedNameNode instanceof Name) {
            return $resolvedNameNode->toString();
        }

        return $name;
    }
}
