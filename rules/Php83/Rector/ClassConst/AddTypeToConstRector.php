<?php

declare (strict_types=1);
namespace Rector\Php83\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MissingConstantFromReflectionException;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Exception\FullyQualifiedNameNotAutoloadedException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php83\Rector\ClassConst\AddTypeToConstRector\AddTypeToConstRectorTest
 */
class AddTypeToConstRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add const to type', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public const TYPE = 'some_type';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public const string TYPE = 'some_type';
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?\PhpParser\Node\Stmt\Class_
    {
        if ($node->isAbstract()) {
            return null;
        }
        $consts = \array_filter($node->stmts, function (Node $stmt) {
            return $stmt instanceof Node\Stmt\ClassConst;
        });
        if ($consts === []) {
            return null;
        }
        try {
            $parents = $this->getParents($node);
            $implementations = $this->getImplementations($node);
            $traits = $this->getTraits($node);
        } catch (FullyQualifiedNameNotAutoloadedException $exception) {
            return null;
        }
        $changes = \false;
        foreach ($consts as $const) {
            // If a type is set, skip
            if ($const->type !== null) {
                continue;
            }
            foreach ($const->consts as $constNode) {
                if ($this->shouldSkipDueToInheritance($constNode, $parents, $implementations, $traits)) {
                    continue;
                }
                if ($this->canBeInheritied($const, $node)) {
                    continue;
                }
                $valueType = $this->findValueType($constNode->value);
            }
            if (($valueType ?? null) === null) {
                continue;
            }
            $const->type = $valueType;
            $changes = \true;
        }
        if (!$changes) {
            return null;
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_CLASS_CONSTANTS;
    }
    /**
     * @param ClassReflection[] $parents
     * @param ClassReflection[] $implementations
     * @param ClassReflection[] $traits
     */
    public function shouldSkipDueToInheritance(Node\Const_ $constNode, array $parents, array $implementations, array $traits) : bool
    {
        foreach ([$parents, $implementations, $traits] as $inheritance) {
            foreach ($inheritance as $inheritanceItem) {
                if ($constNode->name->name === '') {
                    continue;
                }
                try {
                    $inheritanceItem->getConstant($constNode->name->name);
                    return \true;
                } catch (MissingConstantFromReflectionException $exception) {
                }
            }
        }
        return \false;
    }
    private function findValueType(Node\Expr $value) : ?Node\Identifier
    {
        if ($value instanceof Node\Scalar\String_) {
            return new Node\Identifier('string');
        }
        if ($value instanceof Node\Scalar\LNumber) {
            return new Node\Identifier('int');
        }
        if ($value instanceof Node\Scalar\DNumber) {
            return new Node\Identifier('float');
        }
        if ($value instanceof Node\Expr\ConstFetch && $value->name->toLowerString() !== 'null') {
            return new Node\Identifier('bool');
        }
        if ($value instanceof Node\Expr\ConstFetch && $value->name->toLowerString() === 'null') {
            return new Node\Identifier('null');
        }
        if ($value instanceof Node\Expr\Array_) {
            return new Node\Identifier('array');
        }
        return null;
    }
    /**
     * @return ClassReflection[]
     */
    private function getParents(Class_ $class) : array
    {
        $parents = \array_filter([$class->extends]);
        return \array_map(function (Node\Name $name) : ClassReflection {
            if (!$name instanceof FullyQualified) {
                throw new FullyQualifiedNameNotAutoloadedException($name);
            }
            if ($this->reflectionProvider->hasClass($name->toString())) {
                return $this->reflectionProvider->getClass($name->toString());
            }
            throw new FullyQualifiedNameNotAutoloadedException($name);
        }, $parents);
    }
    /**
     * @return ClassReflection[]
     */
    private function getImplementations(Class_ $class) : array
    {
        return \array_map(function (Node\Name $name) : ClassReflection {
            if (!$name instanceof FullyQualified) {
                throw new FullyQualifiedNameNotAutoloadedException($name);
            }
            if ($this->reflectionProvider->hasClass($name->toString())) {
                return $this->reflectionProvider->getClass($name->toString());
            }
            throw new FullyQualifiedNameNotAutoloadedException($name);
        }, $class->implements);
    }
    /**
     * @return ClassReflection[]
     */
    private function getTraits(Class_ $node) : array
    {
        $traits = [];
        foreach ($node->getTraitUses() as $traitUse) {
            $traits = \array_merge($traits, $traitUse->traits);
        }
        return \array_map(function (Node\Name $name) : ClassReflection {
            if (!$name instanceof FullyQualified) {
                throw new FullyQualifiedNameNotAutoloadedException($name);
            }
            if ($this->reflectionProvider->hasClass($name->toString())) {
                return $this->reflectionProvider->getClass($name->toString());
            }
            throw new FullyQualifiedNameNotAutoloadedException($name);
        }, $traits);
    }
    private function canBeInheritied(Node\Stmt\ClassConst $constNode, Class_ $node) : bool
    {
        return !$node->isFinal() && !$constNode->isPrivate();
    }
}
