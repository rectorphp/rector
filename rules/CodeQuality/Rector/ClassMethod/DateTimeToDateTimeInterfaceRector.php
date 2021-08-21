<?php

declare (strict_types=1);
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
final class DateTimeToDateTimeInterfaceRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var string[]
     */
    private const METHODS_RETURNING_CLASS_INSTANCE_MAP = ['add', 'modify', \Rector\Core\ValueObject\MethodName::SET_STATE, 'setDate', 'setISODate', 'setTime', 'setTimestamp', 'setTimezone', 'sub'];
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
    private $returnTypeManipulator;
    /**
     * @var \Rector\CodeQuality\NodeManipulator\ClassMethodParameterTypeManipulator
     */
    private $parameterTypeManipulator;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\Core\NodeAnalyzer\ParamAnalyzer $paramAnalyzer, \Rector\CodeQuality\NodeManipulator\ClassMethodReturnTypeManipulator $returnTypeManipulator, \Rector\CodeQuality\NodeManipulator\ClassMethodParameterTypeManipulator $parameterTypeManipulator)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->paramAnalyzer = $paramAnalyzer;
        $this->returnTypeManipulator = $returnTypeManipulator;
        $this->parameterTypeManipulator = $parameterTypeManipulator;
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
            $this->refactorClassMethod($node);
            return $node;
        }
        return $this->refactorProperty($node);
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::DATE_TIME_INTERFACE;
    }
    private function refactorProperty(\PhpParser\Node\Stmt\Property $node) : ?\PhpParser\Node
    {
        $type = $node->type;
        if ($type === null) {
            return null;
        }
        $isNullable = \false;
        if ($type instanceof \PhpParser\Node\NullableType) {
            $isNullable = \true;
            $type = $type->type;
        }
        if (!$this->isObjectType($type, new \PHPStan\Type\ObjectType('DateTime'))) {
            return null;
        }
        $types = $this->determinePhpDocTypes($node->type);
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $this->phpDocTypeChanger->changeVarType($phpDocInfo, new \PHPStan\Type\UnionType($types));
        $node->type = new \PhpParser\Node\Name\FullyQualified('DateTimeInterface');
        if ($isNullable) {
            $node->type = new \PhpParser\Node\NullableType($node->type);
        }
        return $node;
    }
    /**
     * @return Type[]
     */
    private function determinePhpDocTypes(?\PhpParser\Node $node) : array
    {
        $types = [new \PHPStan\Type\ObjectType('DateTime'), new \PHPStan\Type\ObjectType('DateTimeImmutable')];
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
    private function refactorClassMethod(\PhpParser\Node\Stmt\ClassMethod $node) : void
    {
        $fromObjectType = new \PHPStan\Type\ObjectType('DateTime');
        $replaceIntoType = new \PhpParser\Node\Name\FullyQualified('DateTimeInterface');
        $replacementPhpDocType = new \PHPStan\Type\UnionType([new \PHPStan\Type\ObjectType('DateTime'), new \PHPStan\Type\ObjectType('DateTimeImmutable')]);
        $this->parameterTypeManipulator->refactorFunctionParameters($node, $fromObjectType, $replaceIntoType, $replacementPhpDocType, self::METHODS_RETURNING_CLASS_INSTANCE_MAP);
        if (!$node->returnType instanceof \PhpParser\Node) {
            return;
        }
        $this->returnTypeManipulator->refactorFunctionReturnType($node, $fromObjectType, $replaceIntoType, $replacementPhpDocType);
    }
}
