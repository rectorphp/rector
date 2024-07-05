<?php

declare (strict_types=1);
namespace Rector\Php83\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
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
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(ReflectionProvider $reflectionProvider, StaticTypeMapper $staticTypeMapper)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add type to constants based on their value', [new CodeSample(<<<'CODE_SAMPLE'
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
            $valueTypes = [];
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
                $valueTypes[] = $this->findValueType($constNode->value);
            }
            if ($valueTypes === []) {
                continue;
            }
            if (\count($valueTypes) > 1) {
                $valueTypes = \array_unique($valueTypes, \SORT_REGULAR);
            }
            // once more verify after uniquate
            if (\count($valueTypes) > 1) {
                continue;
            }
            $valueType = \current($valueTypes);
            if (!$valueType instanceof Identifier) {
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
        if ($expr instanceof UnaryPlus || $expr instanceof UnaryMinus) {
            return $this->findValueType($expr->expr);
        }
        if ($expr instanceof String_) {
            return new Identifier('string');
        }
        if ($expr instanceof LNumber) {
            return new Identifier('int');
        }
        if ($expr instanceof DNumber) {
            return new Identifier('float');
        }
        if ($expr instanceof ConstFetch || $expr instanceof ClassConstFetch) {
            if ($expr instanceof ConstFetch && $expr->name->toLowerString() === 'null') {
                return new Identifier('null');
            }
            $type = $this->nodeTypeResolver->getNativeType($expr);
            $nodeType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::PROPERTY);
            if (!$nodeType instanceof Identifier) {
                return null;
            }
            return $nodeType;
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
        return !$class->isFinal() && !$classConst->isPrivate() && !$classConst->isFinal();
    }
}
