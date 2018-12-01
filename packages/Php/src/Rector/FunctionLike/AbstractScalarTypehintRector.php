<?php declare(strict_types=1);

namespace Rector\Php\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use Rector\NodeTypeResolver\Application\ClassLikeNodeCollector;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Php\PhpTypeSupport;
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

    public function __construct(
        DocBlockAnalyzer $docBlockAnalyzer,
        ClassLikeNodeCollector $classLikeNodeCollector,
        bool $enableObjectType = false
    ) {
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->classLikeNodeCollector = $classLikeNodeCollector;

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

    protected function isChangeVendorLockedIn(ClassMethod $classMethodNode): bool
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
                    return false;
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

        $interfaceNames = $this->getClassNodeInterfaceNames($classNode);
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

        if (ctype_upper($possibleSubtype[0]) && $type === 'object') {
            return true;
        }

        return false;
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
     * @return string[]
     */
    private function getClassNodeInterfaceNames(Class_ $classNode): array
    {
        $interfaces = [];
        foreach ($classNode->implements as $implementNode) {
            $interfaces[] = $this->getName($implementNode);
        }

        return $interfaces;
    }
}
