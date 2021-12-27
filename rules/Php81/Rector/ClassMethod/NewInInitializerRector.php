<?php

declare (strict_types=1);
namespace Rector\Php81\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/new_in_initializers
 *
 * @see \Rector\Tests\Php81\Rector\ClassMethod\NewInInitializerRector\NewInInitializerRectorTest
 */
final class NewInInitializerRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace property declaration of new state with direct new', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private Logger $logger;

    public function __construct(
        ?Logger $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private Logger $logger = new NullLogger,
    ) {
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
        if (!$this->isLegalClass($node)) {
            return null;
        }
        $params = $this->matchConstructorParams($node);
        if ($params === null) {
            return null;
        }
        foreach ($params as $param) {
            if (!$param->type instanceof \PhpParser\Node\NullableType) {
                continue;
            }
            /** @var string $paramName */
            $paramName = $this->getName($param->var);
            $toPropertyAssigns = $this->betterNodeFinder->findClassMethodAssignsToLocalProperty($node, $paramName);
            foreach ($toPropertyAssigns as $toPropertyAssign) {
                if (!$toPropertyAssign->expr instanceof \PhpParser\Node\Expr\BinaryOp\Coalesce) {
                    continue;
                }
                /** @var NullableType $currentParamType */
                $currentParamType = $param->type;
                $param->type = $currentParamType->type;
                $coalesce = $toPropertyAssign->expr;
                $param->default = $coalesce->right;
                $this->removeNode($toPropertyAssign);
                $this->processPropertyPromotion($node, $param, $paramName);
            }
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::NEW_INITIALIZERS;
    }
    private function processPropertyPromotion(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PhpParser\Node\Param $param, string $paramName) : void
    {
        $classLike = $this->betterNodeFinder->findParentType($classMethod, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return;
        }
        $property = $classLike->getProperty($paramName);
        if (!$property instanceof \PhpParser\Node\Stmt\Property) {
            return;
        }
        $param->flags = $property->flags;
        $this->removeNode($property);
    }
    private function isLegalClass(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $classLike = $this->betterNodeFinder->findParentType($classMethod, \PhpParser\Node\Stmt\ClassLike::class);
        if ($classLike instanceof \PhpParser\Node\Stmt\Interface_) {
            return \false;
        }
        if ($classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return !$classLike->isAbstract();
        }
        return \true;
    }
    /**
     * @return mixed[]|null
     */
    private function matchConstructorParams(\PhpParser\Node\Stmt\ClassMethod $classMethod)
    {
        if (!$this->isName($classMethod, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            return null;
        }
        if ($classMethod->params === []) {
            return null;
        }
        if ($classMethod->stmts === []) {
            return null;
        }
        return $classMethod->params;
    }
}
