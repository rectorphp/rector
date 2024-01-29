<?php

declare (strict_types=1);
namespace Rector\Php83\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php83\Rector\ClassConst\AddTypeToConstRector\AddTypeToConstRectorTest
 */
final class AddTypeToConstRector extends AbstractRector implements MinPhpVersionInterface
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
    public function refactor(Node $node) : ?Class_
    {
        $className = $this->getName($node);
        if (!\is_string($className)) {
            return null;
        }
        if ($node->isAbstract()) {
            return null;
        }
        $classConsts = $node->getConstants();
        if ($classConsts === []) {
            return null;
        }
        $parentClassReflections = $this->getParentReflections($className);
        $hasChanged = \false;
        foreach ($classConsts as $classConst) {
            $valueType = null;
            // If a type is set, skip
            if ($classConst->type !== null) {
                continue;
            }
            foreach ($classConst->consts as $constNode) {
                if ($this->isConstGuardedByParents($constNode, $parentClassReflections)) {
                    continue;
                }
                if ($this->canBeInherited($classConst, $node)) {
                    continue;
                }
                $valueType = $this->findValueType($constNode->value);
            }
            if (!($valueType ?? null) instanceof Identifier) {
                continue;
            }
            $classConst->type = $valueType;
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_CLASS_CONSTANTS;
    }
    /**
     * @param ClassReflection[] $parentClassReflections
     */
    public function isConstGuardedByParents(Const_ $const, array $parentClassReflections) : bool
    {
        $constantName = $this->getName($const);
        foreach ($parentClassReflections as $parentClassReflection) {
            if ($parentClassReflection->hasConstant($constantName)) {
                return \true;
            }
        }
        return \false;
    }
    private function findValueType(Expr $expr) : ?Identifier
    {
        if ($expr instanceof String_) {
            return new Identifier('string');
        }
        if ($expr instanceof LNumber) {
            return new Identifier('int');
        }
        if ($expr instanceof DNumber) {
            return new Identifier('float');
        }
        if ($expr instanceof ConstFetch && $expr->name->toLowerString() !== 'null') {
            return new Identifier('bool');
        }
        if ($expr instanceof ConstFetch && $expr->name->toLowerString() === 'null') {
            return new Identifier('null');
        }
        if ($expr instanceof Array_) {
            return new Identifier('array');
        }
        if ($expr instanceof Concat) {
            return new Identifier('string');
        }
        return null;
    }
    /**
     * @return ClassReflection[]
     */
    private function getParentReflections(string $className) : array
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return [];
        }
        $currentClassReflection = $this->reflectionProvider->getClass($className);
        return \array_filter($currentClassReflection->getAncestors(), static function (ClassReflection $classReflection) use($currentClassReflection) : bool {
            return $currentClassReflection !== $classReflection;
        });
    }
    private function canBeInherited(ClassConst $classConst, Class_ $class) : bool
    {
        return !$class->isFinal() && !$classConst->isPrivate();
    }
}
