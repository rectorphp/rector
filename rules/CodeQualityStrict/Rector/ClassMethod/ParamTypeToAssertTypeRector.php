<?php

declare (strict_types=1);
namespace Rector\CodeQualityStrict\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\CodeQualityStrict\NodeFactory\ClassConstFetchFactory;
use Rector\CodeQualityStrict\TypeAnalyzer\SubTypeAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQualityStrict\Rector\ClassMethod\ParamTypeToAssertTypeRector\ParamTypeToAssertTypeRectorTest
 */
final class ParamTypeToAssertTypeRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var ClassConstFetchFactory
     */
    private $classConstFetchFactory;
    /**
     * @var SubTypeAnalyzer
     */
    private $subTypeAnalyzer;
    public function __construct(\Rector\CodeQualityStrict\NodeFactory\ClassConstFetchFactory $classConstFetchFactory, \Rector\CodeQualityStrict\TypeAnalyzer\SubTypeAnalyzer $subTypeAnalyzer)
    {
        $this->classConstFetchFactory = $classConstFetchFactory;
        $this->subTypeAnalyzer = $subTypeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turn @param type to assert type', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param \A|\B $arg
     */
    public function run($arg)
    {

    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    /**
     * @param \A|\B $arg
     */
    public function run($arg)
    {
        \Webmozart\Assert\Assert::isAnyOf($arg, [\A::class, \B::class]);
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        /** @var Type[] $docParamTypes */
        $docParamTypes = $phpDocInfo->getParamTypesByName();
        if ($docParamTypes === []) {
            return null;
        }
        $params = $node->getParams();
        if ($params === []) {
            return null;
        }
        $toBeProcessedTypes = [];
        foreach ($docParamTypes as $paramName => $docParamType) {
            if (!$this->isExclusivelyObjectType($docParamType)) {
                continue;
            }
            /** @var ObjectType|UnionType $docParamType */
            $assertionTypes = $this->getToBeProcessedTypes($params, $paramName, $docParamType);
            if ($assertionTypes === null) {
                continue;
            }
            $variableName = \ltrim($paramName, '$');
            $toBeProcessedTypes[$variableName] = $assertionTypes;
        }
        return $this->processAddTypeAssert($node, $toBeProcessedTypes);
    }
    private function isExclusivelyObjectType(\PHPStan\Type\Type $type) : bool
    {
        if ($type instanceof \PHPStan\Type\ObjectType) {
            return \true;
        }
        if ($type instanceof \PHPStan\Type\UnionType) {
            foreach ($type->getTypes() as $unionedType) {
                if (!$this->isExclusivelyObjectType($unionedType)) {
                    return \false;
                }
            }
            return \true;
        }
        return \false;
    }
    /**
     * @param Param[] $params
     * @param ObjectType|UnionType $type
     * @return ObjectType|UnionType
     */
    private function getToBeProcessedTypes(array $params, string $key, \PHPStan\Type\Type $type) : ?\PHPStan\Type\Type
    {
        foreach ($params as $param) {
            $paramName = \ltrim($key, '$');
            if (!$this->isName($param->var, $paramName)) {
                continue;
            }
            if ($param->type === null) {
                continue;
            }
            // skip if doc type is the same as PHP
            $paramType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
            if ($paramType->equals($type)) {
                continue;
            }
            if ($this->subTypeAnalyzer->isObjectSubType($paramType, $type)) {
                continue;
            }
            return $type;
        }
        return null;
    }
    /**
     * @param array<string, ObjectType|UnionType> $toBeProcessedTypes
     */
    private function processAddTypeAssert(\PhpParser\Node\Stmt\ClassMethod $classMethod, array $toBeProcessedTypes) : \PhpParser\Node\Stmt\ClassMethod
    {
        $assertStatements = [];
        foreach ($toBeProcessedTypes as $variableName => $requiredType) {
            $classConstFetches = $this->classConstFetchFactory->createFromType($requiredType);
            $arguments = [new \PhpParser\Node\Expr\Variable($variableName)];
            if (\count($classConstFetches) > 1) {
                $arguments[] = $classConstFetches;
                $methodName = 'isAnyOf';
            } else {
                $arguments[] = $classConstFetches[0];
                $methodName = 'isAOf';
            }
            $args = $this->nodeFactory->createArgs($arguments);
            $staticCall = $this->nodeFactory->createStaticCall('Webmozart\\Assert\\Assert', $methodName, $args);
            $assertStatements[] = new \PhpParser\Node\Stmt\Expression($staticCall);
        }
        return $this->addStatements($classMethod, $assertStatements);
    }
    /**
     * @param Expression[] $assertStatements
     */
    private function addStatements(\PhpParser\Node\Stmt\ClassMethod $classMethod, array $assertStatements) : \PhpParser\Node\Stmt\ClassMethod
    {
        if (!isset($classMethod->stmts[0])) {
            foreach ($assertStatements as $assertStatement) {
                $classMethod->stmts[] = $assertStatement;
            }
        } else {
            foreach ($assertStatements as $assertStatement) {
                $this->addNodeBeforeNode($assertStatement, $classMethod->stmts[0]);
            }
        }
        return $classMethod;
    }
}
