<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Php80\NodeAnalyzer\EnumConstListClassDetector;
use Rector\Php80\NodeAnalyzer\EnumParamAnalyzer;
use Rector\Php81\NodeFactory\EnumFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php80\Rector\Class_\ConstantListClassToEnumRector\ConstantListClassToEnumRectorTest
 */
final class ConstantListClassToEnumRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\EnumConstListClassDetector
     */
    private $enumConstListClassDetector;
    /**
     * @readonly
     * @var \Rector\Php81\NodeFactory\EnumFactory
     */
    private $enumFactory;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\EnumParamAnalyzer
     */
    private $enumParamAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    public function __construct(\Rector\Php80\NodeAnalyzer\EnumConstListClassDetector $enumConstListClassDetector, \Rector\Php81\NodeFactory\EnumFactory $enumFactory, \Rector\Php80\NodeAnalyzer\EnumParamAnalyzer $enumParamAnalyzer, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver, \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover $phpDocTagRemover)
    {
        $this->enumConstListClassDetector = $enumConstListClassDetector;
        $this->enumFactory = $enumFactory;
        $this->enumParamAnalyzer = $enumParamAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->phpDocTagRemover = $phpDocTagRemover;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Upgrade constant list classes to full blown enum', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class Direction
{
    public const LEFT = 'left';

    public const RIGHT = 'right';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
enum Direction
{
    case LEFT;

    case RIGHT;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class, \PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param Class_|ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Stmt\Class_) {
            if (!$this->enumConstListClassDetector->detect($node)) {
                return null;
            }
            return $this->enumFactory->createFromClass($node);
        }
        return $this->refactorClassMethod($node);
    }
    private function refactorClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        // enum param types doc requires a docblock
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (!$phpDocInfo instanceof \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo) {
            return null;
        }
        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromClassMethod($classMethod);
        if (!$methodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return null;
        }
        // refactor params
        $haveParamsChanged = $this->refactorParams($methodReflection, $phpDocInfo, $classMethod);
        $hasReturnChanged = $this->refactorReturn($phpDocInfo, $classMethod);
        if ($haveParamsChanged) {
            return $classMethod;
        }
        if ($hasReturnChanged) {
            return $classMethod;
        }
        return null;
    }
    private function getParamByName(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $desiredParamName) : ?\PhpParser\Node\Param
    {
        foreach ($classMethod->params as $param) {
            if (!$this->nodeNameResolver->isName($param, $desiredParamName)) {
                continue;
            }
            return $param;
        }
        return null;
    }
    private function refactorParams(\PHPStan\Reflection\MethodReflection $methodReflection, \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $hasNodeChanged = \false;
        $parametersAcceptor = \PHPStan\Reflection\ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
        foreach ($parametersAcceptor->getParameters() as $parameterReflection) {
            $enumLikeClass = $this->enumParamAnalyzer->matchParameterClassName($parameterReflection, $phpDocInfo);
            if ($enumLikeClass === null) {
                continue;
            }
            $param = $this->getParamByName($classMethod, $parameterReflection->getName());
            if (!$param instanceof \PhpParser\Node\Param) {
                continue;
            }
            // change and remove
            $param->type = new \PhpParser\Node\Name\FullyQualified($enumLikeClass);
            $hasNodeChanged = \true;
            /** @var ParamTagValueNode $paramTagValueNode */
            $paramTagValueNode = $phpDocInfo->getParamTagValueByName($parameterReflection->getName());
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $paramTagValueNode);
        }
        return $hasNodeChanged;
    }
    private function refactorReturn(\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo $phpDocInfo, \PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $returnType = $this->enumParamAnalyzer->matchReturnClassName($phpDocInfo);
        if ($returnType === null) {
            return \false;
        }
        $classMethod->returnType = new \PhpParser\Node\Name\FullyQualified($returnType);
        /** @var ReturnTagValueNode $returnTagValueNode */
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $returnTagValueNode);
        return \true;
    }
}
