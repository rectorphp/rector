<?php declare(strict_types=1);

namespace Rector\NodeAnalyzer;

use PhpParser\Builder\Trait_;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use Rector\BetterReflection\Reflector\SmartClassReflector;
use Rector\Node\Attribute;

final class ClassLikeAnalyzer
{
    /**
     * @var SmartClassReflector
     */
    private $smartClassReflector;

    public function __construct(SmartClassReflector $smartClassReflector)
    {
        $this->smartClassReflector = $smartClassReflector;
    }

    public function isAnonymousClassNode(Node $node): bool
    {
        return $node instanceof Class_ && $node->isAnonymous();
    }

    public function isNormalClass(Node $node): bool
    {
        return $node instanceof Class_ && ! $node->isAnonymous();
    }

    /**
     * @param Class_|Interface_|Trait_ $classLikeNode
     * @return string[]
     */
    public function resolveTypeAndParentTypes(ClassLike $classLikeNode): array
    {
        $types = [];

        if (! $this->isAnonymousClassNode($classLikeNode)) {
            $className = $this->resolveNameNode($classLikeNode);
            $types[] = $className;

            if ($classLikeNode instanceof Class_ || $classLikeNode instanceof Interface_) {
                $types = array_merge($types, $this->resolveExtendsTypes($classLikeNode, $className));
            }
        } else {
            $types = array_merge($types, $this->resolveExtendsTypes($classLikeNode));
        }

        if ($classLikeNode instanceof Class_) {
            $types = array_merge($types, $this->resolveImplementsTypes($classLikeNode));
        }

        return $types;
    }

    /**
     * @param Name|ClassLike $node
     */
    private function resolveNameNode(Node $node): string
    {
        $name = (string) $node->getAttribute(Attribute::CLASS_NAME);
        if ($name) {
            return $name;
        }

        $namespacedName = $node->getAttribute('namespacedName');
        if ($namespacedName instanceof FullyQualified) {
            return $namespacedName->toString();
        }

        $nameNode = $node->getAttribute(Attribute::RESOLVED_NAME);
        if ($nameNode instanceof Name) {
            return $nameNode->toString();
        }

        if ($node instanceof Name) {
            return $node->toString();
        }

        return $node->name instanceof Identifier ? $node->name->toString() : '';
    }

    /**
     * @param Class_|Interface_ $classLikeNode
     * @return string[]
     */
    private function resolveExtendsTypes(ClassLike $classLikeNode, ?string $className = null): array
    {
        if (! $classLikeNode->extends) {
            return [];
        }

        return $this->smartClassReflector->getClassParents($className, $classLikeNode);
    }

    /**
     * @return string[]
     */
    private function resolveImplementsTypes(Class_ $classNode): array
    {
        $types = [];

        $interfaces = $classNode->implements;
        foreach ($interfaces as $interface) {
            $types[] = $interface->toString();
        }

        return $types;
    }
}
