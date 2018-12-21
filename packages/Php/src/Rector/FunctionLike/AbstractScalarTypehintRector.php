<?php declare(strict_types=1);

namespace Rector\Php\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use Rector\NodeTypeResolver\Application\ClassLikeNodeCollector;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\Php\AbstractTypeInfo;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Php\PhpTypeSupport;
use Rector\PhpParser\Node\Maintainer\FunctionLikeMaintainer;
use Rector\Rector\AbstractRector;

/**
 * @see https://wiki.php.net/rfc/scalar_type_hints_v5
 * @see https://github.com/nikic/TypeUtil
 * @see https://github.com/nette/type-fixer
 * @see https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/3258
 */
abstract class AbstractScalarTypehintRector extends AbstractRector
{
    /**
     * @var string
     */
    protected const HAS_NEW_INHERITED_TYPE = 'has_new_inherited_return_type';

    /**
     * @var DocBlockAnalyzer
     */
    protected $docBlockAnalyzer;

    /**
     * @var ClassLikeNodeCollector
     */
    protected $classLikeNodeCollector;

    /**
     * @var FunctionLikeMaintainer
     */
    protected $functionLikeMaintainer;

    public function __construct(
        DocBlockAnalyzer $docBlockAnalyzer,
        ClassLikeNodeCollector $classLikeNodeCollector,
        FunctionLikeMaintainer $functionLikeMaintainer,
        bool $enableObjectType = false
    ) {
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->classLikeNodeCollector = $classLikeNodeCollector;
        $this->functionLikeMaintainer = $functionLikeMaintainer;

        if ($enableObjectType) {
            PhpTypeSupport::enableType('object');
        }
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Function_::class, ClassMethod::class];
    }

    protected function isChangeVendorLockedIn(ClassMethod $classMethodNode, int $paramPosition): bool
    {
        if ($this->hasParentClassOrImplementsInterface($classMethodNode) === false) {
            return false;
        }

        $methodName = $this->getName($classMethodNode);
        // @todo extract to some "inherited parent method" service

        /** @var string|null $parentClassName */
        $parentClassName = $classMethodNode->getAttribute(Attribute::PARENT_CLASS_NAME);

        if ($parentClassName !== null) {
            $parentClassNode = $this->classLikeNodeCollector->findClass($parentClassName);
            if ($parentClassNode) {
                $parentMethodNode = $parentClassNode->getMethod($methodName);
                // @todo validate type is conflicting
                // parent class method in local scope → it's ok
                if ($parentMethodNode) {
                    // parent method has no type → we cannot change it here
                    return isset($parentMethodNode->params[$paramPosition]) && $parentMethodNode->params[$paramPosition]->type === null;
                }

                // if not, look for it's parent parent - @todo recursion
            }

            if (method_exists($parentClassName, $methodName)) {
                // @todo validate type is conflicting
                // parent class method in external scope → it's not ok
                return true;

                // if not, look for it's parent parent - @todo recursion
            }
        }

        /** @var Class_ $classNode */
        $classNode = $classMethodNode->getAttribute(Attribute::CLASS_NODE);

        $interfaceNames = $this->getClassLikeNodeParentInterfaceNames($classNode);
        foreach ($interfaceNames as $interfaceName) {
            $interface = $this->classLikeNodeCollector->findInterface($interfaceName);
            if ($interface) {
                // parent class method in local scope → it's ok
                // @todo validate type is conflicting
                if ($interface->getMethod($methodName)) {
                    return false;
                }
            }

            if (method_exists($interfaceName, $methodName)) {
                // parent class method in external scope → it's not ok
                // @todo validate type is conflicting
                return true;
            }
        }

        return false;
    }

    /**
     * @param Name|NullableType $possibleSubtype
     * @param Name|NullableType $type
     */
    protected function isSubtypeOf(Node $possibleSubtype, Node $type): bool
    {
        $isNullable = $type instanceof NullableType;
        if ($isNullable) {
            $type = $type->type;
        }

        if ($possibleSubtype instanceof NullableType) {
            $possibleSubtype = $possibleSubtype->type;
        }

        $possibleSubtype = $possibleSubtype->toString();
        $type = $type->toString();

        if (is_a($possibleSubtype, $type, true)) {
            return true;
        }

        if (in_array($possibleSubtype, ['array', 'Traversable'], true) && $type === 'iterable') {
            return true;
        }

        if (in_array($possibleSubtype, ['array', 'ArrayIterator'], true) && $type === 'countable') {
            return true;
        }

        if ($type === $possibleSubtype) {
            return true;
        }

        return ctype_upper($possibleSubtype[0]) && $type === 'object';
    }

    /**
     * @param ClassMethod|Param $childClassMethodOrParam
     * @return Name|NullableType|null
     */
    protected function resolveChildType(
        AbstractTypeInfo $returnTypeInfo,
        Node $node,
        Node $childClassMethodOrParam
    ): ?Node {
        if ($returnTypeInfo->getTypeNode() instanceof NullableType) {
            $nakedType = $returnTypeInfo->getTypeNode()->type;
        } else {
            $nakedType = $returnTypeInfo->getTypeNode();
        }

        if ($nakedType === null) {
            return null;
        }

        if ($nakedType->toString() === 'self') {
            $className = $node->getAttribute(Attribute::CLASS_NAME);
            $type = new FullyQualified($className);

            return $returnTypeInfo->isNullable() ? new NullableType($type) : $type;
        }

        if ($nakedType->toString() === 'parent') {
            $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);
            $type = new FullyQualified($parentClassName);

            return $returnTypeInfo->isNullable() ? new NullableType($type) : $type;
        }

        // is namespace the same? use short name
        if ($this->haveSameNamespace($node, $childClassMethodOrParam)) {
            return $returnTypeInfo->getTypeNode();
        }

        // are namespaces different? → FQN name
        return $returnTypeInfo->getFqnTypeNode();
    }

    private function hasParentClassOrImplementsInterface(ClassMethod $classMethodNode): bool
    {
        /** @var ClassLike|null $classNode */
        $classNode = $classMethodNode->getAttribute(Attribute::CLASS_NODE);
        if ($classNode === null) {
            return false;
        }

        if ($classNode instanceof Class_ || $classNode instanceof Interface_) {
            if ($classNode->extends) {
                return true;
            }
        }

        if ($classNode instanceof Class_) {
            return (bool) $classNode->implements;
        }

        return false;
    }

    /**
     * @param Class_|Interface_ $classLikeNode
     * @return string[]
     */
    private function getClassLikeNodeParentInterfaceNames(ClassLike $classLikeNode): array
    {
        $interfaces = [];

        if ($classLikeNode instanceof Class_) {
            foreach ($classLikeNode->implements as $implementNode) {
                $interfaces[] = $this->getName($implementNode);
            }
        }

        if ($classLikeNode instanceof Interface_) {
            foreach ($classLikeNode->extends as $extendNode) {
                $interfaces[] = $this->getName($extendNode);
            }
        }

        return $interfaces;
    }

    private function haveSameNamespace(Node $firstNode, Node $secondNode): bool
    {
        return $firstNode->getAttribute(Attribute::NAMESPACE_NAME)
            === $secondNode->getAttribute(Attribute::NAMESPACE_NAME);
    }
}
