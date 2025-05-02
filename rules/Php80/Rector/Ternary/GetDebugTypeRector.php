<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\ValueObject\PolyfillPackage;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Rector\VersionBonding\Contract\RelatedPolyfillInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php80\Rector\Ternary\GetDebugTypeRector\GetDebugTypeRectorTest
 */
final class GetDebugTypeRector extends AbstractRector implements MinPhpVersionInterface, RelatedPolyfillInterface
{
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::GET_DEBUG_TYPE;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change ternary type resolve to get_debug_type()', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        return get_debug_type($value);
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
        return [Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if (!$this->areValuesIdentical($node)) {
            return null;
        }
        /** @var FuncCall|ClassConstFetch $getClassFuncCallOrClassConstFetchClass */
        $getClassFuncCallOrClassConstFetchClass = $node->if;
        $firstExpr = $getClassFuncCallOrClassConstFetchClass instanceof FuncCall ? $getClassFuncCallOrClassConstFetchClass->getArgs()[0]->value : $getClassFuncCallOrClassConstFetchClass->class;
        return $this->nodeFactory->createFuncCall('get_debug_type', [$firstExpr]);
    }
    public function providePolyfillPackage() : string
    {
        return PolyfillPackage::PHP_80;
    }
    private function shouldSkip(Ternary $ternary) : bool
    {
        if (!$ternary->cond instanceof FuncCall) {
            return \true;
        }
        if ($ternary->cond->isFirstClassCallable()) {
            return \true;
        }
        if (!isset($ternary->cond->getArgs()[0])) {
            return \true;
        }
        if (!$this->isName($ternary->cond, 'is_object')) {
            return \true;
        }
        if (!$ternary->if instanceof FuncCall) {
            if (!$ternary->if instanceof ClassConstFetch) {
                return \true;
            }
            return $this->shouldSkipClassConstFetch($ternary->if);
        }
        if (!$this->isName($ternary->if, 'get_class')) {
            return \true;
        }
        if (!$ternary->else instanceof FuncCall) {
            return \true;
        }
        if ($ternary->else->isFirstClassCallable()) {
            return \true;
        }
        return !$this->isName($ternary->else, 'gettype');
    }
    private function shouldSkipClassConstFetch(ClassConstFetch $classConstFetch) : bool
    {
        if (!$classConstFetch->name instanceof Identifier) {
            return \true;
        }
        return $classConstFetch->name->toString() !== 'class';
    }
    private function areValuesIdentical(Ternary $ternary) : bool
    {
        /** @var FuncCall $isObjectFuncCall */
        $isObjectFuncCall = $ternary->cond;
        if ($isObjectFuncCall->isFirstClassCallable()) {
            return \false;
        }
        $firstExpr = $isObjectFuncCall->getArgs()[0]->value;
        /** @var FuncCall|ClassConstFetch $getClassFuncCallOrClassConstFetchClass */
        $getClassFuncCallOrClassConstFetchClass = $ternary->if;
        if ($getClassFuncCallOrClassConstFetchClass instanceof FuncCall && !$getClassFuncCallOrClassConstFetchClass->args[0] instanceof Arg) {
            return \false;
        }
        $secondExpr = $getClassFuncCallOrClassConstFetchClass instanceof FuncCall ? $getClassFuncCallOrClassConstFetchClass->getArgs()[0]->value : $getClassFuncCallOrClassConstFetchClass->class;
        $gettypeFuncCall = $ternary->else;
        if (!$gettypeFuncCall instanceof FuncCall) {
            return \false;
        }
        if (!$gettypeFuncCall->args[0] instanceof Arg) {
            return \false;
        }
        $thirdExpr = $gettypeFuncCall->args[0]->value;
        if (!$this->nodeComparator->areNodesEqual($firstExpr, $secondExpr)) {
            return \false;
        }
        return $this->nodeComparator->areNodesEqual($firstExpr, $thirdExpr);
    }
}
