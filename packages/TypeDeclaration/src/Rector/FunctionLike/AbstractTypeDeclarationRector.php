<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\Php\AbstractTypeInfo;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\Rector\AbstractRector;

/**
 * @see https://wiki.php.net/rfc/scalar_type_hints_v5
 * @see https://github.com/nikic/TypeUtil
 * @see https://github.com/nette/type-fixer
 * @see https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/3258
 */
abstract class AbstractTypeDeclarationRector extends AbstractRector
{
    /**
     * @var string
     */
    public const HAS_NEW_INHERITED_TYPE = 'has_new_inherited_return_type';

    /**
     * @var DocBlockManipulator
     */
    protected $docBlockManipulator;

    /**
     * @var ParsedNodesByType
     */
    protected $parsedNodesByType;

    /**
     * @required
     */
    public function autowireAbstractTypeDeclarationRector(
        DocBlockManipulator $docBlockManipulator,
        ParsedNodesByType $parsedNodesByType
    ): void {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->parsedNodesByType = $parsedNodesByType;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Function_::class, ClassMethod::class];
    }

    protected function isChangeVendorLockedIn(ClassMethod $classMethod, int $paramPosition): bool
    {
        if (! $this->hasParentClassOrImplementsInterface($classMethod)) {
            return false;
        }

        $methodName = $this->getName($classMethod);

        // @todo extract to some "inherited parent method" service

        /** @var string|null $parentClassName */
        $parentClassName = $classMethod->getAttribute(AttributeKey::PARENT_CLASS_NAME);

        if ($parentClassName !== null) {
            $parentClassNode = $this->parsedNodesByType->findClass($parentClassName);
            if ($parentClassNode !== null) {
                $parentMethodNode = $parentClassNode->getMethod($methodName);
                // @todo validate type is conflicting
                // parent class method in local scope → it's ok
                if ($parentMethodNode !== null) {
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

        $classNode = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_ && ! $classNode instanceof Interface_) {
            return false;
        }

        $interfaceNames = $this->getClassLikeNodeParentInterfaceNames($classNode);
        foreach ($interfaceNames as $interfaceName) {
            $interface = $this->parsedNodesByType->findInterface($interfaceName);
            if ($interface !== null) {
                // parent class method in local scope → it's ok
                // @todo validate type is conflicting
                if ($interface->getMethod($methodName) !== null) {
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
     * @param Name|NullableType|Identifier $possibleSubtype
     * @param Name|NullableType|Identifier $type
     */
    protected function isSubtypeOf(Node $possibleSubtype, Node $type, string $kind): bool
    {
        $type = $type instanceof NullableType ? $type->type : $type;

        if ($possibleSubtype instanceof NullableType) {
            $possibleSubtype = $possibleSubtype->type;
        }

        $possibleSubtype = $possibleSubtype->toString();
        $type = $type->toString();

        if ($kind === 'return') {
            if (is_a($possibleSubtype, $type, true)) {
                return true;
            }
        } elseif ($kind === 'param') {
            if (is_a($possibleSubtype, $type, true)) {
                return true;
            }
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
     * @param ClassMethod|Param $node
     * @return Name|NullableType|Identifier|null
     */
    protected function resolveChildType(AbstractTypeInfo $returnTypeInfo, Node $node): ?Node
    {
        $nakedType = $returnTypeInfo->getTypeNode() instanceof NullableType ? $returnTypeInfo->getTypeNode()->type : $returnTypeInfo->getTypeNode();

        if ($nakedType === null) {
            return null;
        }

        if ($nakedType->toString() === 'self') {
            $className = $node->getAttribute(AttributeKey::CLASS_NAME);
            if ($className === null) {
                throw new ShouldNotHappenException(__METHOD__ . '() on line ' . __LINE__);
            }

            $type = new FullyQualified($className);

            return $returnTypeInfo->isNullable() ? new NullableType($type) : $type;
        }

        if ($nakedType->toString() === 'parent') {
            $parentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);
            if ($parentClassName === null) {
                throw new ShouldNotHappenException(__METHOD__ . '() on line ' . __LINE__);
            }

            $type = new FullyQualified($parentClassName);

            return $returnTypeInfo->isNullable() ? new NullableType($type) : $type;
        }

        // are namespaces different? → FQN name
        return $returnTypeInfo->getFqnTypeNode();
    }

    private function hasParentClassOrImplementsInterface(ClassMethod $classMethod): bool
    {
        $classNode = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
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
     * @param Class_|Interface_ $classLike
     * @return string[]
     */
    private function getClassLikeNodeParentInterfaceNames(ClassLike $classLike): array
    {
        $interfaces = [];

        if ($classLike instanceof Class_) {
            foreach ($classLike->implements as $implementNode) {
                $interfaceName = $this->getName($implementNode);
                if ($interfaceName === null) {
                    continue;
                }

                $interfaces[] = $interfaceName;
            }
        }

        if ($classLike instanceof Interface_) {
            foreach ($classLike->extends as $extendNode) {
                $interfaceName = $this->getName($extendNode);
                if ($interfaceName === null) {
                    continue;
                }

                $interfaces[] = $interfaceName;
            }
        }

        return $interfaces;
    }
}
