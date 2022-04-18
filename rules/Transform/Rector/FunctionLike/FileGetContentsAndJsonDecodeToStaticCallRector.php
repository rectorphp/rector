<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\ValueObject\StaticCallRecipe;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220418\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\FunctionLike\FileGetContentsAndJsonDecodeToStaticCallRector\FileGetContentsAndJsonDecodeToStaticCallRectorTest
 */
final class FileGetContentsAndJsonDecodeToStaticCallRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var \Rector\Transform\ValueObject\StaticCallRecipe
     */
    private $staticCallRecipe;
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Merge 2 function calls to static call', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function load($filePath)
    {
        $fileGetContents = file_get_contents($filePath);
        return json_decode($fileGetContents, true);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function load($filePath)
    {
        return FileLoader::loadJson($filePath);
    }
}
CODE_SAMPLE
, [new \Rector\Transform\ValueObject\StaticCallRecipe('FileLoader', 'loadJson')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\FunctionLike::class];
    }
    /**
     * @param FunctionLike $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $stmts = $node->getStmts();
        if ($stmts === null) {
            return null;
        }
        $hasChanged = \false;
        $previousStmt = null;
        foreach ($stmts as $stmt) {
            if ($this->processStmt($previousStmt, $stmt)) {
                $hasChanged = \true;
                /** @var Stmt $previousStmt */
                $this->removeNode($previousStmt);
            }
            $previousStmt = $stmt;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $staticCallRecipe = $configuration[0] ?? null;
        \RectorPrefix20220418\Webmozart\Assert\Assert::isInstanceOf($staticCallRecipe, \Rector\Transform\ValueObject\StaticCallRecipe::class);
        $this->staticCallRecipe = $staticCallRecipe;
    }
    private function createStaticCall(\PhpParser\Node\Expr\FuncCall $fileGetContentsFuncCall) : \PhpParser\Node\Expr\StaticCall
    {
        $fullyQualified = new \PhpParser\Node\Name\FullyQualified($this->staticCallRecipe->getClassName());
        return new \PhpParser\Node\Expr\StaticCall($fullyQualified, $this->staticCallRecipe->getMethodName(), $fileGetContentsFuncCall->getArgs());
    }
    private function processStmt(?\PhpParser\Node\Stmt $previousStmt, \PhpParser\Node\Stmt $currentStmt) : bool
    {
        if (!$previousStmt instanceof \PhpParser\Node\Stmt\Expression) {
            return \false;
        }
        $previousExpr = $previousStmt->expr;
        if (!$previousExpr instanceof \PhpParser\Node\Expr\Assign) {
            return \false;
        }
        $previousAssign = $previousExpr;
        if (!$previousAssign->expr instanceof \PhpParser\Node\Expr\FuncCall) {
            return \false;
        }
        if (!$this->isName($previousAssign->expr, 'file_get_contents')) {
            return \false;
        }
        $fileGetContentsFuncCall = $previousAssign->expr;
        if ($currentStmt instanceof \PhpParser\Node\Stmt\Return_) {
            return $this->refactorReturnAndAssign($currentStmt, $fileGetContentsFuncCall);
        }
        if (!$currentStmt instanceof \PhpParser\Node\Stmt\Expression) {
            return \false;
        }
        if (!$currentStmt->expr instanceof \PhpParser\Node\Expr\Assign) {
            return \false;
        }
        return $this->refactorReturnAndAssign($currentStmt->expr, $fileGetContentsFuncCall);
    }
    /**
     * @param \PhpParser\Node\Expr\Assign|\PhpParser\Node\Stmt\Return_ $currentStmt
     */
    private function refactorReturnAndAssign($currentStmt, \PhpParser\Node\Expr\FuncCall $fileGetContentsFuncCall) : bool
    {
        if (!$currentStmt->expr instanceof \PhpParser\Node\Expr\FuncCall) {
            return \false;
        }
        if (!$this->isName($currentStmt->expr, 'json_decode')) {
            return \false;
        }
        $currentStmt->expr = $this->createStaticCall($fileGetContentsFuncCall);
        return \true;
    }
}
