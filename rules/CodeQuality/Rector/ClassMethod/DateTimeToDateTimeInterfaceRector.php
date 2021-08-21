<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\CodeQuality\NodeManipulator\ClassMethodParameterTypeManipulator;
use Rector\CodeQuality\NodeManipulator\ClassMethodReturnTypeManipulator;
use Rector\Core\NodeAnalyzer\ParamAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\DateTimeToDateTimeInterfaceRectorTest
 */
final class DateTimeToDateTimeInterfaceRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var string[]
     */
    private const METHODS_RETURNING_CLASS_INSTANCE_MAP = [
        'add', 'modify', MethodName::SET_STATE, 'setDate', 'setISODate', 'setTime', 'setTimestamp', 'setTimezone', 'sub',
    ];

    /**
     * @var string
     */
    private const DATE_TIME = 'DateTime';

    public function __construct(
        private PhpDocTypeChanger $phpDocTypeChanger,
        private ParamAnalyzer $paramAnalyzer,
        private ClassMethodReturnTypeManipulator $classMethodReturnTypeManipulator,
        private ClassMethodParameterTypeManipulator $classMethodParameterTypeManipulator
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Changes DateTime type-hint to DateTimeInterface', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass {
    public function methodWithDateTime(\DateTime $dateTime)
    {
        return true;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass {
    /**
     * @param \DateTime|\DateTimeImmutable $dateTime
     */
    public function methodWithDateTime(\DateTimeInterface $dateTime)
    {
        return true;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Property::class];
    }

    /**
     * @param ClassMethod|Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassMethod) {
            $this->refactorClassMethod($node);
            return $node;
        }

        return $this->refactorProperty($node);
    }

    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::DATE_TIME_INTERFACE;
    }

    private function refactorProperty(Property $property): ?Node
    {
        $type = $property->type;
        if ($type === null) {
            return null;
        }

        $isNullable = false;
        if ($type instanceof NullableType) {
            $isNullable = true;
            $type = $type->type;
        }
        if (! $this->isObjectType($type, new ObjectType(self::DATE_TIME))) {
            return null;
        }

        $types = $this->determinePhpDocTypes($property->type);

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $this->phpDocTypeChanger->changeVarType($phpDocInfo, new UnionType($types));

        $property->type = new FullyQualified('DateTimeInterface');
        if ($isNullable) {
            $property->type = new NullableType($property->type);
        }
        return $property;
    }

    /**
     * @return Type[]
     */
    private function determinePhpDocTypes(?Node $node): array
    {
        $types = [new ObjectType(self::DATE_TIME), new ObjectType('DateTimeImmutable')];

        if ($this->canHaveNullType($node)) {
            $types[] = new NullType();
        }

        return $types;
    }

    private function canHaveNullType(?Node $node): bool
    {
        if ($node instanceof Param) {
            return $this->paramAnalyzer->isNullable($node);
        }

        return $node instanceof NullableType;
    }

    private function refactorClassMethod(ClassMethod $classMethod): void
    {
        if ($this->shouldSkipExactlyReturnDateTime($classMethod)) {
            return;
        }

        $fromObjectType = new ObjectType(self::DATE_TIME);
        $fullyQualified = new FullyQualified('DateTimeInterface');
        $unionType = new UnionType([new ObjectType(self::DATE_TIME), new ObjectType('DateTimeImmutable')]);

        $this->classMethodParameterTypeManipulator->refactorFunctionParameters(
            $classMethod,
            $fromObjectType,
            $fullyQualified,
            $unionType,
            self::METHODS_RETURNING_CLASS_INSTANCE_MAP
        );
        if (! $classMethod->returnType instanceof Node) {
            return;
        }
        $this->classMethodReturnTypeManipulator->refactorFunctionReturnType(
            $classMethod,
            $fromObjectType,
            $fullyQualified,
            $unionType
        );
    }

    private function shouldSkipExactlyReturnDateTime(ClassMethod $classMethod): bool
    {
        $return = $this->betterNodeFinder->findFirst(
            (array) $classMethod->stmts,
            fn (Node $node): bool => $node instanceof Return_
        );
        if (! $return instanceof Return_) {
            return false;
        }

        if (! $return->expr instanceof Expr) {
            return false;
        }

        return $return->expr instanceof New_ && $return->expr->class instanceof Name && $this->nodeNameResolver->isName(
            $return->expr->class,
            self::DATE_TIME
        );
    }
}
