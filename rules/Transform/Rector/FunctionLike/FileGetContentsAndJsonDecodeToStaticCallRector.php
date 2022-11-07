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
use PhpParser\Node\VariadicPlaceholder;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\ValueObject\StaticCallRecipe;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202211\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\FunctionLike\FileGetContentsAndJsonDecodeToStaticCallRector\FileGetContentsAndJsonDecodeToStaticCallRectorTest
 */
final class FileGetContentsAndJsonDecodeToStaticCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var \Rector\Transform\ValueObject\StaticCallRecipe
     */
    private $staticCallRecipe;
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Merge 2 function calls to static call', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, [new StaticCallRecipe('FileLoader', 'loadJson')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FunctionLike::class];
    }
    /**
     * @param FunctionLike $node
     */
    public function refactor(Node $node) : ?Node
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
        Assert::isInstanceOf($staticCallRecipe, StaticCallRecipe::class);
        $this->staticCallRecipe = $staticCallRecipe;
    }
    private function createStaticCall(FuncCall $fileGetContentsFuncCall) : StaticCall
    {
        $fullyQualified = new FullyQualified($this->staticCallRecipe->getClassName());
        return new StaticCall($fullyQualified, $this->staticCallRecipe->getMethodName(), $fileGetContentsFuncCall->isFirstClassCallable() ? [new VariadicPlaceholder()] : $fileGetContentsFuncCall->getArgs());
    }
    private function processStmt(?Stmt $previousStmt, Stmt $currentStmt) : bool
    {
        if (!$previousStmt instanceof Expression) {
            return \false;
        }
        $previousExpr = $previousStmt->expr;
        if (!$previousExpr instanceof Assign) {
            return \false;
        }
        $previousAssign = $previousExpr;
        if (!$previousAssign->expr instanceof FuncCall) {
            return \false;
        }
        if (!$this->isName($previousAssign->expr, 'file_get_contents')) {
            return \false;
        }
        $fileGetContentsFuncCall = $previousAssign->expr;
        if ($currentStmt instanceof Return_) {
            return $this->refactorReturnAndAssign($currentStmt, $fileGetContentsFuncCall);
        }
        if (!$currentStmt instanceof Expression) {
            return \false;
        }
        if (!$currentStmt->expr instanceof Assign) {
            return \false;
        }
        return $this->refactorReturnAndAssign($currentStmt->expr, $fileGetContentsFuncCall);
    }
    /**
     * @param \PhpParser\Node\Stmt\Return_|\PhpParser\Node\Expr\Assign $currentStmt
     */
    private function refactorReturnAndAssign($currentStmt, FuncCall $fileGetContentsFuncCall) : bool
    {
        if (!$currentStmt->expr instanceof FuncCall) {
            return \false;
        }
        if (!$this->isName($currentStmt->expr, 'json_decode')) {
            return \false;
        }
        $currentStmt->expr = $this->createStaticCall($fileGetContentsFuncCall);
        return \true;
    }
}
