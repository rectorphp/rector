<?php

declare(strict_types=1);

namespace Rector\Restoration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Restoration\Type\ConstantReturnToParamTypeConverter;
use Rector\Restoration\ValueObject\InferParamFromClassMethodReturn;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\Restoration\Rector\ClassMethod\InferParamFromClassMethodReturnRector\InferParamFromClassMethodReturnRectorTest
 */
final class InferParamFromClassMethodReturnRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const INFER_PARAMS_FROM_CLASS_METHOD_RETURNS = 'infer_param_from_class_method_returns';

    /**
     * @var InferParamFromClassMethodReturn[]
     */
    private $inferParamFromClassMethodReturn = [];

    public function __construct(
        private ReturnTypeInferer $returnTypeInferer,
        private ConstantReturnToParamTypeConverter $constantReturnToParamTypeConverter,
        private PhpDocTypeChanger $phpDocTypeChanger
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change @param doc based on another method return type', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function getNodeTypes(): array
    {
        return [String_::class];
    }

    public function process(Node $node)
    {
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function getNodeTypes(): array
    {
        return [String_::class];
    }

    /**
     * @param String_ $node
     */
    public function process(Node $node)
    {
    }
}
CODE_SAMPLE
,
                [
                    self::INFER_PARAMS_FROM_CLASS_METHOD_RETURNS => [
                        new InferParamFromClassMethodReturn('SomeClass', 'process', 'getNodeTypes'),
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        // must be exactly 1 param
        if (count($node->params) !== 1) {
            return null;
        }

        $firstParam = $node->params[0];
        $paramName = $this->getName($firstParam);

        foreach ($this->inferParamFromClassMethodReturn as $singleInferParamFromClassMethodReturn) {
            $returnClassMethod = $this->matchReturnClassMethod($node, $singleInferParamFromClassMethodReturn);
            if (! $returnClassMethod instanceof ClassMethod) {
                continue;
            }

            $returnType = $this->returnTypeInferer->inferFunctionLike($returnClassMethod);

            $currentPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

            $paramType = $this->constantReturnToParamTypeConverter->convert($returnType);
            if ($paramType instanceof MixedType) {
                continue;
            }

            if ($this->isParamDocTypeEqualToPhpType($firstParam, $paramType)) {
                return null;
            }

            $this->phpDocTypeChanger->changeParamType($currentPhpDocInfo, $paramType, $firstParam, $paramName);

            return $node;
        }

        return null;
    }

    /**
     * @param array<string, InferParamFromClassMethodReturn[]> $configuration
     */
    public function configure(array $configuration): void
    {
        $inferParamsFromClassMethodReturns = $configuration[self::INFER_PARAMS_FROM_CLASS_METHOD_RETURNS] ?? [];
        Assert::allIsInstanceOf($inferParamsFromClassMethodReturns, InferParamFromClassMethodReturn::class);

        $this->inferParamFromClassMethodReturn = $inferParamsFromClassMethodReturns;
    }

    private function matchReturnClassMethod(
        ClassMethod $classMethod,
        InferParamFromClassMethodReturn $inferParamFromClassMethodReturn
    ): ?ClassMethod {
        $scope = $classMethod->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        if (! $classReflection->isSubclassOf($inferParamFromClassMethodReturn->getClass())) {
            return null;
        }

        if (! $this->isName($classMethod->name, $inferParamFromClassMethodReturn->getParamMethod())) {
            return null;
        }

        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return null;
        }

        return $classLike->getMethod($inferParamFromClassMethodReturn->getReturnMethod());
    }

    private function isParamDocTypeEqualToPhpType(Param $param, Type $paramType): bool
    {
        $currentParamType = $this->nodeTypeResolver->getStaticType($param);
        return $currentParamType->equals($paramType);
    }
}
