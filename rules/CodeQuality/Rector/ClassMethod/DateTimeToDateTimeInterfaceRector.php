<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\CodeQuality\Rector\ClassMethod;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Name\FullyQualified;
use RectorPrefix20220606\PhpParser\Node\NullableType;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Property;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\PHPStan\Type\NullType;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use RectorPrefix20220606\Rector\CodeQuality\NodeManipulator\ClassMethodParameterTypeManipulator;
use RectorPrefix20220606\Rector\CodeQuality\NodeManipulator\ClassMethodReturnTypeManipulator;
use RectorPrefix20220606\Rector\Core\NodeAnalyzer\CallAnalyzer;
use RectorPrefix20220606\Rector\Core\NodeAnalyzer\ParamAnalyzer;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\ValueObject\MethodName;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersionFeature;
use RectorPrefix20220606\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\ClassMethod\DateTimeToDateTimeInterfaceRector\DateTimeToDateTimeInterfaceRectorTest
 */
final class DateTimeToDateTimeInterfaceRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @var string[]
     */
    private const METHODS_RETURNING_CLASS_INSTANCE_MAP = ['add', 'modify', MethodName::SET_STATE, 'setDate', 'setISODate', 'setTime', 'setTimestamp', 'setTimezone', 'sub'];
    /**
     * @var string
     */
    private const DATE_TIME = 'DateTime';
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeManipulator\ClassMethodReturnTypeManipulator
     */
    private $classMethodReturnTypeManipulator;
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeManipulator\ClassMethodParameterTypeManipulator
     */
    private $classMethodParameterTypeManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\CallAnalyzer
     */
    private $callAnalyzer;
    public function __construct(PhpDocTypeChanger $phpDocTypeChanger, ParamAnalyzer $paramAnalyzer, ClassMethodReturnTypeManipulator $classMethodReturnTypeManipulator, ClassMethodParameterTypeManipulator $classMethodParameterTypeManipulator, CallAnalyzer $callAnalyzer)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->paramAnalyzer = $paramAnalyzer;
        $this->classMethodReturnTypeManipulator = $classMethodReturnTypeManipulator;
        $this->classMethodParameterTypeManipulator = $classMethodParameterTypeManipulator;
        $this->callAnalyzer = $callAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes DateTime type-hint to DateTimeInterface', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass {
    public function methodWithDateTime(\DateTime $dateTime)
    {
        return true;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
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
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Property::class];
    }
    /**
     * @param ClassMethod|Property $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof ClassMethod) {
            return $this->refactorClassMethod($node);
        }
        return $this->refactorProperty($node);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DATE_TIME_INTERFACE;
    }
    private function refactorProperty(Property $property) : ?Node
    {
        $type = $property->type;
        if ($type === null) {
            return null;
        }
        $isNullable = \false;
        if ($type instanceof NullableType) {
            $isNullable = \true;
            $type = $type->type;
        }
        if (!$this->isObjectType($type, new ObjectType(self::DATE_TIME))) {
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
    private function determinePhpDocTypes(?Node $node) : array
    {
        $types = [new ObjectType(self::DATE_TIME), new ObjectType('DateTimeImmutable')];
        if ($this->canHaveNullType($node)) {
            $types[] = new NullType();
        }
        return $types;
    }
    private function canHaveNullType(?Node $node) : bool
    {
        if ($node instanceof Param) {
            return $this->paramAnalyzer->isNullable($node);
        }
        return $node instanceof NullableType;
    }
    private function refactorClassMethod(ClassMethod $classMethod) : ?ClassMethod
    {
        if ($this->shouldSkipExactlyReturnDateTime($classMethod)) {
            return null;
        }
        $fromObjectType = new ObjectType(self::DATE_TIME);
        $fullyQualified = new FullyQualified('DateTimeInterface');
        $unionType = new UnionType([new ObjectType(self::DATE_TIME), new ObjectType('DateTimeImmutable')]);
        $this->classMethodParameterTypeManipulator->refactorFunctionParameters($classMethod, $fromObjectType, $fullyQualified, $unionType, self::METHODS_RETURNING_CLASS_INSTANCE_MAP);
        if (!$classMethod->returnType instanceof Node) {
            return null;
        }
        return $this->classMethodReturnTypeManipulator->refactorFunctionReturnType($classMethod, $fromObjectType, $fullyQualified, $unionType);
    }
    private function shouldSkipExactlyReturnDateTime(ClassMethod $classMethod) : bool
    {
        $return = $this->betterNodeFinder->findFirstInFunctionLikeScoped($classMethod, function (Node $node) : bool {
            return $node instanceof Return_;
        });
        if (!$return instanceof Return_) {
            return \false;
        }
        if (!$return->expr instanceof Expr) {
            return \false;
        }
        if (!$this->callAnalyzer->isNewInstance($return->expr)) {
            return \false;
        }
        $type = $this->nodeTypeResolver->getType($return->expr);
        return $type instanceof ObjectType && $type->getClassName() === self::DATE_TIME;
    }
}
