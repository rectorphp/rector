<?php

declare(strict_types=1);

namespace Rector\Restoration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\Restoration\ValueObject\InferParamFromClassMethodReturn;
use Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;
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
    public const INFER_PARAMS_FROM_CLASS_METHOD_RETURNS = 'infer_params_from_class_method_returns';

    /**
     * @var InferParamFromClassMethodReturn[]
     */
    private $inferParamFromClassMethodReturn = [];

    /**
     * @var ReturnTypeInferer
     */
    private $returnTypeInferer;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    public function __construct(ReturnTypeInferer $returnTypeInferer, TypeFactory $typeFactory)
    {
        $this->returnTypeInferer = $returnTypeInferer;
        $this->typeFactory = $typeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change @param doc based on another method return type', [
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
        if ($paramName === null) {
            throw new ShouldNotHappenException();
        }

        foreach ($this->inferParamFromClassMethodReturn as $inferParamFromClassMethodReturn) {
            $returnClassMethod = $this->matchReturnClassMethod($node, $inferParamFromClassMethodReturn);
            if ($returnClassMethod === null) {
                continue;
            }

            $returnType = $this->returnTypeInferer->inferFunctionLike($returnClassMethod);
            if (! $returnType instanceof ConstantStringType && ! $returnType instanceof ConstantArrayType) {
                continue;
            }

            /** @var PhpDocInfo|null $currentPhpDocInfo */
            $currentPhpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
            if ($currentPhpDocInfo === null) {
                $currentPhpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
            }

            $paramType = $this->createParamTypeFromReturnType($returnType);
            $currentPhpDocInfo->changeParamType($paramType, $firstParam, $paramName);

            return $node;
        }

        return null;
    }

    /**
     * @param mixed[] $configuration
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
        if (! $this->isInClassNamed($classMethod, $inferParamFromClassMethodReturn->getClass())) {
            return null;
        }

        if (! $this->isName($classMethod->name, $inferParamFromClassMethodReturn->getParamMethod())) {
            return null;
        }

        $class = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return null;
        }

        return $class->getMethod($inferParamFromClassMethodReturn->getReturnMethod());
    }

    private function createParamTypeFromReturnType($returnType): Type
    {
        $paramTypes = [];
        if ($returnType instanceof ConstantArrayType) {
            $itemType = $returnType->getItemType();

            if ($itemType instanceof ConstantStringType) {
                $paramTypes[] = new ObjectType($itemType->getValue());
            } elseif ($itemType instanceof UnionType) {
                foreach ($itemType->getTypes() as $unionedType) {
                    if ($unionedType instanceof ConstantStringType) {
                        $paramTypes[] = new ObjectType($unionedType->getValue());
                    }
                }
            }
        } else {
            throw new NotImplementedYetException();
        }

        return $this->typeFactory->createMixedPassedOrUnionType($paramTypes);
    }
}
