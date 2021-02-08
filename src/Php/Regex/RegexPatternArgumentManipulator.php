<?php

declare(strict_types=1);

namespace Rector\Core\Php\Regex;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class RegexPatternArgumentManipulator
{
    /**
     * @var array<string, int>
     */
    private const FUNCTIONS_WITH_PATTERNS_TO_ARGUMENT_POSITION = [
        'preg_match' => 0,
        'preg_replace_callback_array' => 0,
        'preg_replace_callback' => 0,
        'preg_replace' => 0,
        'preg_match_all' => 0,
        'preg_split' => 0,
        'preg_grep' => 0,
    ];

    /**
     * @var array<string, array<string, int>>
     */
    private const STATIC_METHODS_WITH_PATTERNS_TO_ARGUMENT_POSITION = [
        Strings::class => [
            'match' => 1,
            'matchAll' => 1,
            'replace' => 1,
            'split' => 1,
        ],
    ];

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        BetterStandardPrinter $betterStandardPrinter,
        NodeNameResolver $nodeNameResolver,
        NodeTypeResolver $nodeTypeResolver,
        NodeRepository $nodeRepository
    ) {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeRepository = $nodeRepository;
    }

    /**
     * @return String_[]
     */
    public function matchCallArgumentWithRegexPattern(Expr $expr): array
    {
        if ($expr instanceof FuncCall) {
            return $this->processFuncCall($expr);
        }

        if ($expr instanceof StaticCall) {
            return $this->processStaticCall($expr);
        }

        return [];
    }

    /**
     * @return String_[]
     */
    private function processFuncCall(FuncCall $funcCall): array
    {
        foreach (self::FUNCTIONS_WITH_PATTERNS_TO_ARGUMENT_POSITION as $functionName => $argumentPosition) {
            if (! $this->nodeNameResolver->isName($funcCall, $functionName)) {
                continue;
            }

            if (! isset($funcCall->args[$argumentPosition])) {
                return [];
            }

            return $this->resolveArgumentValues($funcCall->args[$argumentPosition]->value);
        }

        return [];
    }

    /**
     * @return String_[]
     */
    private function processStaticCall(StaticCall $staticCall): array
    {
        foreach (self::STATIC_METHODS_WITH_PATTERNS_TO_ARGUMENT_POSITION as $type => $methodNamesToArgumentPosition) {
            if (! $this->nodeTypeResolver->isObjectType($staticCall->class, $type)) {
                continue;
            }

            foreach ($methodNamesToArgumentPosition as $methodName => $argumentPosition) {
                if (! $this->nodeNameResolver->isName($staticCall->name, $methodName)) {
                    continue;
                }

                if (! isset($staticCall->args[$argumentPosition])) {
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
    private function resolveArgumentValues(Expr $expr): array
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
            return $this->resolveClassConstFetchValue($expr);
        }

        return [];
    }

    /**
     * @return Assign[]
     */
    private function findAssignerForVariable(Variable $variable): array
    {
        $classMethod = $variable->getAttribute(AttributeKey::METHOD_NODE);
        if (! $classMethod instanceof ClassMethod) {
            return [];
        }

        return $this->betterNodeFinder->find([$classMethod], function (Node $node) use ($variable): ?Assign {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $this->betterStandardPrinter->areNodesEqual($node->var, $variable)) {
                return null;
            }

            return $node;
        });
    }

    /**
     * @return String_[]
     */
    private function resolveClassConstFetchValue(ClassConstFetch $classConstFetch): array
    {
        $classConstNode = $this->nodeRepository->findClassConstByClassConstFetch($classConstFetch);
        if (! $classConstNode instanceof ClassConst) {
            return [];
        }

        if ($classConstNode->consts[0]->value instanceof String_) {
            return [$classConstNode->consts[0]->value];
        }

        return [];
    }
}
