<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
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

    public function __construct(
        private PhpDocTypeChanger $phpDocTypeChanger,
        private ParamAnalyzer $paramAnalyzer,
        private ClassMethodReturnTypeManipulator $returnTypeManipulator,
        private ClassMethodParameterTypeManipulator $parameterTypeManipulator
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

    private function refactorProperty(Property $node): ?Node
    {
        $type = $node->type;
        if ($type === null) {
            return null;
        }

        $isNullable = false;
        if ($type instanceof NullableType) {
            $isNullable = true;
            $type = $type->type;
        }
        if (! $this->isObjectType($type, new ObjectType('DateTime'))) {
            return null;
        }


        $types = $this->determinePhpDocTypes($node->type);

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $this->phpDocTypeChanger->changeVarType($phpDocInfo, new UnionType($types));

        $node->type = new FullyQualified('DateTimeInterface');
        if ($isNullable) {
            $node->type = new NullableType($node->type);
        }
        return $node;
    }

    /**
     * @return Type[]
     */
    private function determinePhpDocTypes(?Node $node): array
    {
        $types = [
            new ObjectType('DateTime'),
            new ObjectType('DateTimeImmutable')
        ];

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

    private function refactorClassMethod(ClassMethod $node): void
    {
        $fromObjectType = new ObjectType('DateTime');
        $replaceIntoType = new FullyQualified('DateTimeInterface');
        $replacementPhpDocType = new UnionType([
            new ObjectType('DateTime'),
            new ObjectType('DateTimeImmutable')
        ]);

        $this->parameterTypeManipulator->refactorFunctionParameters(
            $node,
            $fromObjectType,
            $replaceIntoType,
            $replacementPhpDocType,
            self::METHODS_RETURNING_CLASS_INSTANCE_MAP
        );
        if (! $node->returnType instanceof Node) {
            return;
        }
        $this->returnTypeManipulator->refactorFunctionReturnType(
            $node,
            $fromObjectType,
            $replaceIntoType,
            $replacementPhpDocType
        );
    }
}
