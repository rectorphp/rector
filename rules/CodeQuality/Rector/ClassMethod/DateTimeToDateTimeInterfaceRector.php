<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
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
use Rector\Core\NodeAnalyzer\CallAnalyzer;
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
final class DateTimeToDateTimeInterfaceRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var string[]
     */
    private const METHODS_RETURNING_CLASS_INSTANCE_MAP = ['add', 'modify', \Rector\Core\ValueObject\MethodName::SET_STATE, 'setDate', 'setISODate', 'setTime', 'setTimestamp', 'setTimezone', 'sub'];
    /**
     * @var string
     */
    private const DATE_TIME = 'DateTime';
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @var \Rector\Core\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    /**
     * @var \Rector\CodeQuality\NodeManipulator\ClassMethodReturnTypeManipulator
     */
    private $classMethodReturnTypeManipulator;
    /**
     * @var \Rector\CodeQuality\NodeManipulator\ClassMethodParameterTypeManipulator
     */
    private $classMethodParameterTypeManipulator;
    /**
     * @var \Rector\Core\NodeAnalyzer\CallAnalyzer
     */
    private $callAnalyzer;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\Core\NodeAnalyzer\ParamAnalyzer $paramAnalyzer, \Rector\CodeQuality\NodeManipulator\ClassMethodReturnTypeManipulator $classMethodReturnTypeManipulator, \Rector\CodeQuality\NodeManipulator\ClassMethodParameterTypeManipulator $classMethodParameterTypeManipulator, \Rector\Core\NodeAnalyzer\CallAnalyzer $callAnalyzer)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->paramAnalyzer = $paramAnalyzer;
        $this->classMethodReturnTypeManipulator = $classMethodReturnTypeManipulator;
        $this->classMethodParameterTypeManipulator = $classMethodParameterTypeManipulator;
        $this->callAnalyzer = $callAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes DateTime type-hint to DateTimeInterface', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Property::class];
    }
    /**
     * @param ClassMethod|Property $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return $this->refactorClassMethod($node);
        }
        return $this->refactorProperty($node);
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::DATE_TIME_INTERFACE;
    }
    private function refactorProperty(\PhpParser\Node\Stmt\Property $property) : ?\PhpParser\Node
    {
        $type = $property->type;
        if ($type === null) {
            return null;
        }
        $isNullable = \false;
        if ($type instanceof \PhpParser\Node\NullableType) {
            $isNullable = \true;
            $type = $type->type;
        }
        if (!$this->isObjectType($type, new \PHPStan\Type\ObjectType(self::DATE_TIME))) {
            return null;
        }
        $types = $this->determinePhpDocTypes($property->type);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $this->phpDocTypeChanger->changeVarType($phpDocInfo, new \PHPStan\Type\UnionType($types));
        $property->type = new \PhpParser\Node\Name\FullyQualified('DateTimeInterface');
        if ($isNullable) {
            $property->type = new \PhpParser\Node\NullableType($property->type);
        }
        return $property;
    }
    /**
     * @return Type[]
     */
    private function determinePhpDocTypes(?\PhpParser\Node $node) : array
    {
        $types = [new \PHPStan\Type\ObjectType(self::DATE_TIME), new \PHPStan\Type\ObjectType('DateTimeImmutable')];
        if ($this->canHaveNullType($node)) {
            $types[] = new \PHPStan\Type\NullType();
        }
        return $types;
    }
    private function canHaveNullType(?\PhpParser\Node $node) : bool
    {
        if ($node instanceof \PhpParser\Node\Param) {
            return $this->paramAnalyzer->isNullable($node);
        }
        return $node instanceof \PhpParser\Node\NullableType;
    }
    private function refactorClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        if ($this->shouldSkipExactlyReturnDateTime($classMethod)) {
            return null;
        }
        $fromObjectType = new \PHPStan\Type\ObjectType(self::DATE_TIME);
        $fullyQualified = new \PhpParser\Node\Name\FullyQualified('DateTimeInterface');
        $unionType = new \PHPStan\Type\UnionType([new \PHPStan\Type\ObjectType(self::DATE_TIME), new \PHPStan\Type\ObjectType('DateTimeImmutable')]);
        $this->classMethodParameterTypeManipulator->refactorFunctionParameters($classMethod, $fromObjectType, $fullyQualified, $unionType, self::METHODS_RETURNING_CLASS_INSTANCE_MAP);
        if (!$classMethod->returnType instanceof \PhpParser\Node) {
            return null;
        }
        return $this->classMethodReturnTypeManipulator->refactorFunctionReturnType($classMethod, $fromObjectType, $fullyQualified, $unionType);
    }
    private function shouldSkipExactlyReturnDateTime(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $return = $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (\PhpParser\Node $node) : bool {
            return $node instanceof \PhpParser\Node\Stmt\Return_;
        });
        if (!$return instanceof \PhpParser\Node\Stmt\Return_) {
            return \false;
        }
        if (!$return->expr instanceof \PhpParser\Node\Expr) {
            return \false;
        }
        if (!$this->callAnalyzer->isNewInstance($this->betterNodeFinder, $return->expr)) {
            return \false;
        }
        $type = $this->nodeTypeResolver->getType($return->expr);
        return $type instanceof \PHPStan\Type\ObjectType && $type->getClassName() === self::DATE_TIME;
    }
}
