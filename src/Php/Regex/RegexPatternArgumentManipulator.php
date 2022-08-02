<?php

declare (strict_types=1);
namespace Rector\Core\Php\Regex;

use RectorPrefix202208\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\NodeFinder\LocalConstantFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class RegexPatternArgumentManipulator
{
    /**
     * @var array<string, int>
     */
    private const FUNCTIONS_WITH_PATTERNS_TO_ARGUMENT_POSITION = ['preg_match' => 0, 'preg_replace_callback_array' => 0, 'preg_replace_callback' => 0, 'preg_replace' => 0, 'preg_match_all' => 0, 'preg_split' => 0, 'preg_grep' => 0];
    /**
     * @var array<string, array<string, int>>
     */
    private const STATIC_METHODS_WITH_PATTERNS_TO_ARGUMENT_POSITION = [Strings::class => ['match' => 1, 'matchAll' => 1, 'replace' => 1, 'split' => 1]];
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\NodeFinder\LocalConstantFinder
     */
    private $localConstantFinder;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, LocalConstantFinder $localConstantFinder, NodeComparator $nodeComparator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->localConstantFinder = $localConstantFinder;
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @return String_[]
     * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\StaticCall $call
     */
    public function matchCallArgumentWithRegexPattern($call) : array
    {
        if ($call instanceof FuncCall) {
            return $this->processFuncCall($call);
        }
        return $this->processStaticCall($call);
    }
    /**
     * @return String_[]
     */
    private function processFuncCall(FuncCall $funcCall) : array
    {
        foreach (self::FUNCTIONS_WITH_PATTERNS_TO_ARGUMENT_POSITION as $functionName => $argumentPosition) {
            if (!$this->nodeNameResolver->isName($funcCall, $functionName)) {
                continue;
            }
            if (!isset($funcCall->args[$argumentPosition])) {
                return [];
            }
            if (!$funcCall->args[$argumentPosition] instanceof Arg) {
                return [];
            }
            return $this->resolveArgumentValues($funcCall->args[$argumentPosition]->value);
        }
        return [];
    }
    /**
     * @return String_[]
     */
    private function processStaticCall(StaticCall $staticCall) : array
    {
        foreach (self::STATIC_METHODS_WITH_PATTERNS_TO_ARGUMENT_POSITION as $type => $methodNamesToArgumentPosition) {
            if (!$this->nodeTypeResolver->isObjectType($staticCall->class, new ObjectType($type))) {
                continue;
            }
            foreach ($methodNamesToArgumentPosition as $methodName => $argumentPosition) {
                if (!$this->nodeNameResolver->isName($staticCall->name, $methodName)) {
                    continue;
                }
                if (!isset($staticCall->args[$argumentPosition])) {
                    return [];
                }
                if (!$staticCall->args[$argumentPosition] instanceof Arg) {
                    return [];
                }
                return $this->resolveArgumentValues($staticCall->args[$argumentPosition]->value);
            }
        }
        return [];
    }
    /**
     * @return String_[]
     */
    private function resolveArgumentValues(Expr $expr) : array
    {
        if ($expr instanceof String_) {
            return [$expr];
        }
        if ($expr instanceof Variable) {
            $strings = [];
            $assignNodes = $this->findAssignerForVariable($expr);
            foreach ($assignNodes as $assignNode) {
                if ($assignNode->expr instanceof String_) {
                    $strings[] = $assignNode->expr;
                }
            }
            return $strings;
        }
        if ($expr instanceof ClassConstFetch) {
            return $this->matchClassConstFetchStringValue($expr);
        }
        return [];
    }
    /**
     * @return Assign[]
     */
    private function findAssignerForVariable(Variable $variable) : array
    {
        $classMethod = $this->betterNodeFinder->findParentType($variable, ClassMethod::class);
        if (!$classMethod instanceof ClassMethod) {
            return [];
        }
        return $this->betterNodeFinder->find([$classMethod], function (Node $node) use($variable) : bool {
            if (!$node instanceof Assign) {
                return \false;
            }
            return $this->nodeComparator->areNodesEqual($node->var, $variable);
        });
    }
    /**
     * @return String_[]
     */
    private function matchClassConstFetchStringValue(ClassConstFetch $classConstFetch) : array
    {
        $classConst = $this->localConstantFinder->match($classConstFetch);
        if (!$classConst instanceof Const_) {
            return [];
        }
        if ($classConst->value instanceof String_) {
            return [$classConst->value];
        }
        return [];
    }
}
