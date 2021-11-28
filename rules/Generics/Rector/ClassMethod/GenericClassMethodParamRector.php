<?php

declare(strict_types=1);

namespace Rector\Generics\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Generics\ValueObject\GenericClassMethodParam;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\GenericClassMethodParamRectorTest
 */
final class GenericClassMethodParamRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const GENERIC_CLASS_METHOD_PARAMS = 'generic_class_method_params';

    /**
     * @var GenericClassMethodParam[]
     */
    private array $genericClassMethodParams = [];

    public function __construct(
        private PhpDocTypeChanger $phpDocTypeChanger,
        private PropertyNaming $propertyNaming,
        private VisibilityManipulator $visibilityManipulator,
    ) {
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
    public function refactor(Node $node): ClassMethod|null
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        foreach ($this->genericClassMethodParams as $genericClassMethodParam) {
            if (! $classReflection->implementsInterface($genericClassMethodParam->getClassType())) {
                continue;
            }

            if (! $this->isName($node, $genericClassMethodParam->getMethodName())) {
                continue;
            }

            // we have a match :)
            if (! $node->isPublic()) {
                $this->visibilityManipulator->makePublic($node);
            }

            $this->refactorParam($node, $genericClassMethodParam);
        }

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Make class methods generic based on implemented interface', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass implements SomeInterface
{
    private method getParams(SomeSpecificType $someParam)
    {
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
final class SomeClass implements SomeInterface
{
    /**
     * @param SomeSpecificType $someParam
     */
    public method getParams(ParamInterface $someParam)
    {
    }
}
CODE_SAMPLE
            ,
                [
                    self::GENERIC_CLASS_METHOD_PARAMS => [
                        new GenericClassMethodParam('SomeInterface', 'getParams', 0, 'ParamInterface'),
                    ],
                ]
            ),
        ]);
    }

    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        $makeClassMethodGenerics = $configuration[self::GENERIC_CLASS_METHOD_PARAMS] ?? $configuration;
        Assert::allIsAOf($makeClassMethodGenerics, GenericClassMethodParam::class);

        $this->genericClassMethodParams = $makeClassMethodGenerics;
    }

    private function refactorParam(ClassMethod $classMethod, GenericClassMethodParam $genericClassMethodParam): void
    {
        $genericParam = $classMethod->params[$genericClassMethodParam->getParamPosition()] ?? null;

        if (! $genericParam instanceof Param) {
            $paramName = $this->propertyNaming->fqnToVariableName(
                new ObjectType($genericClassMethodParam->getParamGenericType())
            );

            $param = new Param(new Variable($paramName));
            $param->type = new FullyQualified($genericClassMethodParam->getParamGenericType());

            $classMethod->params[$genericClassMethodParam->getParamPosition()] = $param;

        // 2. has a parameter?
        } else {
            $oldParamClassName = null;

            // change type to generic
            if ($genericParam->type !== null) {
                $oldParamClassName = $this->getName($genericParam->type);
                if ($oldParamClassName === $genericClassMethodParam->getParamGenericType()) {
                    // if the param type is correct, skip it
                    return;
                }
            }

            // change type to generic
            $genericParam->type = new FullyQualified($genericClassMethodParam->getParamGenericType());

            // update phpdoc
            if ($oldParamClassName === null) {
                return;
            }

            $this->refactorPhpDocInfo($classMethod, $genericParam, $oldParamClassName);
        }
    }

    private function refactorPhpDocInfo(ClassMethod $classMethod, Param $param, string $oldParamClassName): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);

        // add @param doc
        $paramName = $this->getName($param);

        $this->phpDocTypeChanger->changeParamType(
            $phpDocInfo,
            new ObjectType($oldParamClassName),
            $param,
            $paramName
        );
    }
}
