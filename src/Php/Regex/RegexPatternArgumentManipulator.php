<?php

declare (strict_types=1);
namespace Rector\Core\Php\Regex;

use RectorPrefix20211020\Nette\Utils\Strings;
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
use Rector\NodeTypeResolver\Node\AttributeKey;
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
    private const STATIC_METHODS_WITH_PATTERNS_TO_ARGUMENT_POSITION = [\RectorPrefix20211020\Nette\Utils\Strings::class => ['match' => 1, 'matchAll' => 1, 'replace' => 1, 'split' => 1]];
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\Core\PhpParser\NodeFinder\LocalConstantFinder
     */
    private $localConstantFinder;
    /**
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver, \Rector\Core\PhpParser\NodeFinder\LocalConstantFinder $localConstantFinder, \Rector\Core\PhpParser\Comparing\NodeComparator $nodeComparator)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->localConstantFinder = $localConstantFinder;
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @return String_[]
     */
    public function matchCallArgumentWithRegexPattern(\PhpParser\Node\Expr $expr) : array
    {
        if ($expr instanceof \PhpParser\Node\Expr\FuncCall) {
            return $this->processFuncCall($expr);
        }
        if ($expr instanceof \PhpParser\Node\Expr\StaticCall) {
            return $this->processStaticCall($expr);
        }
        return [];
    }
    /**
     * @return String_[]
     */
    private function processFuncCall(\PhpParser\Node\Expr\FuncCall $funcCall) : array
    {
        foreach (self::FUNCTIONS_WITH_PATTERNS_TO_ARGUMENT_POSITION as $functionName => $argumentPosition) {
            if (!$this->nodeNameResolver->isName($funcCall, $functionName)) {
                continue;
            }
            if (!isset($funcCall->args[$argumentPosition])) {
                return [];
            }
            if (!$funcCall->args[$argumentPosition] instanceof \PhpParser\Node\Arg) {
                return [];
            }
            return $this->resolveArgumentValues($funcCall->args[$argumentPosition]->value);
        }
        return [];
    }
    /**
     * @return String_[]
     */
    private function processStaticCall(\PhpParser\Node\Expr\StaticCall $staticCall) : array
    {
        foreach (self::STATIC_METHODS_WITH_PATTERNS_TO_ARGUMENT_POSITION as $type => $methodNamesToArgumentPosition) {
            if (!$this->nodeTypeResolver->isObjectType($staticCall->class, new \PHPStan\Type\ObjectType($type))) {
                continue;
            }
            foreach ($methodNamesToArgumentPosition as $methodName => $argumentPosition) {
                if (!$this->nodeNameResolver->isName($staticCall->name, $methodName)) {
                    continue;
                }
                if (!isset($staticCall->args[$argumentPosition])) {
                    return [];
                }
                if (!$staticCall->args[$argumentPosition] instanceof \PhpParser\Node\Arg) {
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
    private function resolveArgumentValues(\PhpParser\Node\Expr $expr) : array
    {
        if ($expr instanceof \PhpParser\Node\Scalar\String_) {
            return [$expr];
        }
        if ($expr instanceof \PhpParser\Node\Expr\Variable) {
            $strings = [];
            $assignNodes = $this->findAssignerForVariable($expr);
            foreach ($assignNodes as $assignNode) {
                if ($assignNode->expr instanceof \PhpParser\Node\Scalar\String_) {
                    $strings[] = $assignNode->expr;
                }
            }
            return $strings;
        }
        if ($expr instanceof \PhpParser\Node\Expr\ClassConstFetch) {
            return $this->matchClassConstFetchStringValue($expr);
        }
        return [];
    }
    /**
     * @return Assign[]
     */
    private function findAssignerForVariable(\PhpParser\Node\Expr\Variable $variable) : array
    {
        $classMethod = $variable->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::METHOD_NODE);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return [];
        }
        return $this->betterNodeFinder->find([$classMethod], function (\PhpParser\Node $node) use($variable) : ?Assign {
            if (!$node instanceof \PhpParser\Node\Expr\Assign) {
                return null;
            }
            if (!$this->nodeComparator->areNodesEqual($node->var, $variable)) {
                return null;
            }
            return $node;
        });
    }
    /**
     * @return String_[]
     */
    private function matchClassConstFetchStringValue(\PhpParser\Node\Expr\ClassConstFetch $classConstFetch) : array
    {
        $classConst = $this->localConstantFinder->match($classConstFetch);
        if (!$classConst instanceof \PhpParser\Node\Const_) {
            return [];
        }
        if ($classConst->value instanceof \PhpParser\Node\Scalar\String_) {
            return [$classConst->value];
        }
        return [];
    }
}
