<?php

declare(strict_types=1);

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
final class ConstantListClassToEnumRector extends AbstractRector
{
    public function __construct(
        private readonly EnumConstListClassDetector $enumConstListClassDetector,
        private readonly EnumFactory $enumFactory,
        private readonly EnumParamAnalyzer $enumParamAnalyzer,
        private readonly ReflectionResolver $reflectionResolver,
        private readonly PhpDocTagRemover $phpDocTagRemover
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Upgrade constant list classes to full blown enum', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class Direction
{
    public const LEFT = 'left';

    public const RIGHT = 'right';
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
enum Direction
{
    case LEFT;

    case RIGHT;
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
        return [Class_::class, ClassMethod::class];
    }

    /**
     * @param Class_|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Class_) {
            if (! $this->enumConstListClassDetector->detect($node)) {
                return null;
            }

            return $this->enumFactory->createFromClass($node);
        }

        return $this->refactorClassMethod($node);
    }

    private function refactorClassMethod(ClassMethod $classMethod): ?ClassMethod
    {
        // enum param types doc requires a docblock
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($classMethod);
        if (! $phpDocInfo instanceof PhpDocInfo) {
            return null;
        }

        $methodReflection = $this->reflectionResolver->resolveMethodReflectionFromClassMethod($classMethod);
        if (! $methodReflection instanceof MethodReflection) {
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

    private function getParamByName(ClassMethod $classMethod, string $desiredParamName): ?Param
    {
        foreach ($classMethod->params as $param) {
            if (! $this->nodeNameResolver->isName($param, $desiredParamName)) {
                continue;
            }

            return $param;
        }

        return null;
    }

    private function refactorParams(
        MethodReflection $methodReflection,
        PhpDocInfo $phpDocInfo,
        ClassMethod $classMethod
    ): bool {
        $hasNodeChanged = false;

        $parametersAcceptor = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants());
        foreach ($parametersAcceptor->getParameters() as $parameterReflection) {
            $enumLikeClass = $this->enumParamAnalyzer->matchParameterClassName($parameterReflection, $phpDocInfo);
            if ($enumLikeClass === null) {
                continue;
            }

            $param = $this->getParamByName($classMethod, $parameterReflection->getName());
            if (! $param instanceof Param) {
                continue;
            }

            // change and remove
            $param->type = new FullyQualified($enumLikeClass);
            $hasNodeChanged = true;

            /** @var ParamTagValueNode $paramTagValueNode */
            $paramTagValueNode = $phpDocInfo->getParamTagValueByName($parameterReflection->getName());
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $paramTagValueNode);
        }

        return $hasNodeChanged;
    }

    private function refactorReturn(PhpDocInfo $phpDocInfo, ClassMethod $classMethod): bool
    {
        $returnType = $this->enumParamAnalyzer->matchReturnClassName($phpDocInfo);
        if ($returnType === null) {
            return false;
        }

        $classMethod->returnType = new FullyQualified($returnType);

        /** @var ReturnTagValueNode $returnTagValueNode */
        $returnTagValueNode = $phpDocInfo->getReturnTagValue();
        $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $returnTagValueNode);

        return true;
    }
}
