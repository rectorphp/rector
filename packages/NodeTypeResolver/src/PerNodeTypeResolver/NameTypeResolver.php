<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;

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
            $parentClassName = $nameNode->getAttribute(AttributeKey::PARENT_CLASS_NAME);
            if ($parentClassName === null) {
                return [];
            }

            return [$parentClassName];
        }

        $fullyQualifiedName = $this->resolveFullyQualifiedName($nameNode, $nameNode->toString());
        if ($fullyQualifiedName === null) {
            return [];
        }

        return [$fullyQualifiedName];
    }

    private function resolveFullyQualifiedName(Node $nameNode, string $name): string
    {
        if (in_array($name, ['self', 'static', 'this'], true)) {
            /** @var string|null $class */
            $class = $nameNode->getAttribute(AttributeKey::CLASS_NAME);
            if ($class === null) {
                throw new ShouldNotHappenException();
            }

            return $class;
        }

        if ($name === 'parent') {
            // @todo not sure which parent though
            /** @var string|null $class */
            $class = $nameNode->getAttribute(AttributeKey::PARENT_CLASS_NAME);
            if ($class === null) {
                throw new ShouldNotHappenException();
            }

            return $class;
        }

        /** @var Name|null $resolvedNameNode */
        $resolvedNameNode = $nameNode->getAttribute(AttributeKey::RESOLVED_NAME);
        if ($resolvedNameNode instanceof Name) {
            return $resolvedNameNode->toString();
        }

        return $name;
    }
}
