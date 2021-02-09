<?php

declare(strict_types=1);

namespace Rector\Restoration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
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
 * @sponsor Thanks https://github.com/eonx-com for sponsoring this rule
 *
 * @see \Rector\Restoration\Tests\Rector\ClassMethod\InferParamFromClassMethodReturnRector\InferParamFromClassMethodReturnRectorTest
 */
final class InferParamFromClassMethodReturnRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const PARAM_FROM_CLASS_METHOD_RETURNS = 'param_from_class_method_returns';

    /**
     * @var InferParamFromClassMethodReturn[]
     */
    private $inferParamFromClassMethodReturn = [];

    /**
     * @var ReturnTypeInferer
     */
    private $returnTypeInferer;

    /**
     * @var ConstantReturnToParamTypeConverter
     */
    private $constantReturnToParamTypeConverter;

    /**
     * @var PhpDocTypeChanger
     */
    private $phpDocTypeChanger;

    public function __construct(
        ReturnTypeInferer $returnTypeInferer,
        ConstantReturnToParamTypeConverter $constantReturnToParamTypeConverter,
        PhpDocTypeChanger $phpDocTypeChanger
    ) {
        $this->returnTypeInferer = $returnTypeInferer;
        $this->constantReturnToParamTypeConverter = $constantReturnToParamTypeConverter;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
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
                    self::PARAM_FROM_CLASS_METHOD_RETURNS => [
                        new InferParamFromClassMethodReturn('SomeClass', 'process', 'getNodeTypes'),
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
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

        foreach ($this->inferParamFromClassMethodReturn as $inferParamFromClassMethodReturn) {
            $returnClassMethod = $this->matchReturnClassMethod($node, $inferParamFromClassMethodReturn);
            if (! $returnClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
                continue;
            }

            $returnType = $this->returnTypeInferer->inferFunctionLike($returnClassMethod);

            $currentPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

            $paramType = $this->constantReturnToParamTypeConverter->convert($returnType);
            if (! $paramType instanceof Type) {
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
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $inferParamsFromClassMethodReturns = $configuration[self::PARAM_FROM_CLASS_METHOD_RETURNS] ?? [];
        Assert::allIsInstanceOf($inferParamsFromClassMethodReturns, InferParamFromClassMethodReturn::class);

        $this->inferParamFromClassMethodReturn = $inferParamsFromClassMethodReturns;
    }

    private function matchReturnClassMethod(
        ClassMethod $classMethod,
        InferParamFromClassMethodReturn $inferParamFromClassMethodReturn
    ): ?ClassMethod {
        if (! $this->nodeNameResolver->isInClassNamed($classMethod, $inferParamFromClassMethodReturn->getClass())) {
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
        $currentParamType = $this->getObjectType($param);
        if ($currentParamType instanceof UnionType) {
            $currentParamType = $currentParamType->getTypes()[0];
        }

        return $currentParamType->equals($paramType);
    }
}
