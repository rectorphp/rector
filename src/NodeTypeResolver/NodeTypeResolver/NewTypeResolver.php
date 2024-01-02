<?php

declare (strict_types=1);
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
use Rector\Enum\ObjectReference;
use Rector\NodeAnalyzer\ClassAnalyzer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\ObjectWithoutClassTypeWithParentTypes;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
/**
 * @implements NodeTypeResolverInterface<New_>
 */
final class NewTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    public function __construct(NodeNameResolver $nodeNameResolver, ClassAnalyzer $classAnalyzer)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classAnalyzer = $classAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [New_::class];
    }
    /**
     * @param New_ $node
     */
    public function resolve(Node $node) : Type
    {
        if ($node->class instanceof Name) {
            $className = $this->nodeNameResolver->getName($node->class);
            if (!\in_array($className, [ObjectReference::SELF, ObjectReference::PARENT], \true)) {
                return new ObjectType($className);
            }
        }
        $isAnonymousClass = $this->classAnalyzer->isAnonymousClass($node->class);
        if ($isAnonymousClass) {
            return $this->resolveAnonymousClassType($node);
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            // new node probably
            return new MixedType();
        }
        return $scope->getType($node);
    }
    private function resolveAnonymousClassType(New_ $new) : ObjectWithoutClassType
    {
        if (!$new->class instanceof Class_) {
            return new ObjectWithoutClassType();
        }
        $directParentTypes = [];
        /** @var Class_ $class */
        $class = $new->class;
        if ($class->extends instanceof Name) {
            $parentClass = (string) $class->extends;
            $directParentTypes[] = new FullyQualifiedObjectType($parentClass);
        }
        foreach ($class->implements as $implement) {
            $parentClass = (string) $implement;
            $directParentTypes[] = new FullyQualifiedObjectType($parentClass);
        }
        if ($directParentTypes !== []) {
            return new ObjectWithoutClassTypeWithParentTypes($directParentTypes);
        }
        return new ObjectWithoutClassType();
    }
}
