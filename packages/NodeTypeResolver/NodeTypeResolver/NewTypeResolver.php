<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\TypeFactory\UnionTypeFactory;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;

final class NewTypeResolver implements NodeTypeResolverInterface
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private ClassAnalyzer $classAnalyzer,
        private UnionTypeFactory $unionTypeFactory
    ) {
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses(): array
    {
        return [New_::class];
    }

    /**
     * @param New_ $node
     */
    public function resolve(Node $node): Type
    {
        if ($node->class instanceof Name) {
            $className = $this->nodeNameResolver->getName($node->class);
            if (! in_array($className, ['self', 'parent'], true)) {
                return new ObjectType($className);
            }
        }

        $isAnonymousClass = $this->classAnalyzer->isAnonymousClass($node->class);
        if ($isAnonymousClass) {
            return $this->resolveAnonymousClassType($node);
        }

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            // new node probably
            return new MixedType();
        }

        return $scope->getType($node);
    }

    private function resolveAnonymousClassType(New_ $new): ObjectWithoutClassType
    {
        if (! $new->class instanceof Class_) {
            return new ObjectWithoutClassType();
        }

        $types = [];

        /** @var Class_ $class */
        $class = $new->class;
        if ($class->extends !== null) {
            $parentClass = (string) $class->extends;
            $types[] = new FullyQualifiedObjectType($parentClass);
        }

        foreach ($class->implements as $implement) {
            $parentClass = (string) $implement;
            $types[] = new FullyQualifiedObjectType($parentClass);
        }

        if (count($types) > 1) {
            $unionType = $this->unionTypeFactory->createUnionObjectType($types);
            return new ObjectWithoutClassType($unionType);
        }

        if (count($types) === 1) {
            return new ObjectWithoutClassType($types[0]);
        }

        return new ObjectWithoutClassType();
    }
}
