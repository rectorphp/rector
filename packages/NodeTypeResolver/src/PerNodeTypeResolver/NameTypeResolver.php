<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Name;
use Rector\BetterReflection\Reflector\SmartClassReflector;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;

final class NameTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var SmartClassReflector
     */
    private $smartClassReflector;

    public function __construct(SmartClassReflector $smartClassReflector)
    {
        $this->smartClassReflector = $smartClassReflector;
    }

    public function getNodeClass(): string
    {
        return Name::class;
    }

    /**
     * @param Name $nameNode
     * @return string[]
     */
    public function resolve(Node $nameNode): array
    {
        /** @var Name|null $fqnName */
        $fqnName = $nameNode->getAttribute(Attribute::RESOLVED_NAME);

        $types = [];
        if ($fqnName) {
            $types[] = $fullyQualifiedName = $fqnName->toString();
        }

        if ($fqnName instanceof Name) {
            if ($fullyQualifiedName) {
                $classLikeReflection = $this->smartClassReflector->reflect($fullyQualifiedName);
            }

            $types = array_merge($types, array_keys($classLikeReflection->getInterfaces()));
            $types = array_merge($types, $classLikeReflection->getParentClassNames());
        }

        return $types;
    }
}
