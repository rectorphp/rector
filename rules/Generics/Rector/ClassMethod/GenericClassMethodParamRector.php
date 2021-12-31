<?php

declare (strict_types=1);
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
use RectorPrefix20211231\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\GenericClassMethodParamRectorTest
 */
final class GenericClassMethodParamRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @deprecated
     * @var string
     */
    public const GENERIC_CLASS_METHOD_PARAMS = 'generic_class_method_params';
    /**
     * @var GenericClassMethodParam[]
     */
    private $genericClassMethodParams = [];
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger $phpDocTypeChanger, \Rector\Naming\Naming\PropertyNaming $propertyNaming, \Rector\Privatization\NodeManipulator\VisibilityManipulator $visibilityManipulator)
    {
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->propertyNaming = $propertyNaming;
        $this->visibilityManipulator = $visibilityManipulator;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     * @return \PhpParser\Node\Stmt\ClassMethod|null
     */
    public function refactor(\PhpParser\Node $node)
    {
        $scope = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        if (!$scope instanceof \PHPStan\Analyser\Scope) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof \PHPStan\Reflection\ClassReflection) {
            return null;
        }
        foreach ($this->genericClassMethodParams as $genericClassMethodParam) {
            if (!$classReflection->implementsInterface($genericClassMethodParam->getClassType())) {
                continue;
            }
            if (!$this->isName($node, $genericClassMethodParam->getMethodName())) {
                continue;
            }
            // we have a match :)
            if (!$node->isPublic()) {
                $this->visibilityManipulator->makePublic($node);
            }
            $this->refactorParam($node, $genericClassMethodParam);
        }
        return $node;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Make class methods generic based on implemented interface', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeClass implements SomeInterface
{
    private method getParams(SomeSpecificType $someParam)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
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
, [new \Rector\Generics\ValueObject\GenericClassMethodParam('SomeInterface', 'getParams', 0, 'ParamInterface')])]);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $makeClassMethodGenerics = $configuration[self::GENERIC_CLASS_METHOD_PARAMS] ?? $configuration;
        \RectorPrefix20211231\Webmozart\Assert\Assert::allIsAOf($makeClassMethodGenerics, \Rector\Generics\ValueObject\GenericClassMethodParam::class);
        $this->genericClassMethodParams = $makeClassMethodGenerics;
    }
    private function refactorParam(\PhpParser\Node\Stmt\ClassMethod $classMethod, \Rector\Generics\ValueObject\GenericClassMethodParam $genericClassMethodParam) : void
    {
        $genericParam = $classMethod->params[$genericClassMethodParam->getParamPosition()] ?? null;
        if (!$genericParam instanceof \PhpParser\Node\Param) {
            $paramName = $this->propertyNaming->fqnToVariableName(new \PHPStan\Type\ObjectType($genericClassMethodParam->getParamGenericType()));
            $param = new \PhpParser\Node\Param(new \PhpParser\Node\Expr\Variable($paramName));
            $param->type = new \PhpParser\Node\Name\FullyQualified($genericClassMethodParam->getParamGenericType());
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
            $genericParam->type = new \PhpParser\Node\Name\FullyQualified($genericClassMethodParam->getParamGenericType());
            // update phpdoc
            if ($oldParamClassName === null) {
                return;
            }
            $this->refactorPhpDocInfo($classMethod, $genericParam, $oldParamClassName);
        }
    }
    private function refactorPhpDocInfo(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Param $param, string $oldParamClassName) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        // add @param doc
        $paramName = $this->getName($param);
        $this->phpDocTypeChanger->changeParamType($phpDocInfo, new \PHPStan\Type\ObjectType($oldParamClassName), $param, $paramName);
    }
}
