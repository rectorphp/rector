<?php declare(strict_types=1);

namespace Rector\Php\Regex;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\Printer\BetterStandardPrinter;

final class RegexPatternArgumentManipulator
{
    /**
     * @var int[]
     */
    private $functionsWithPatternsToArgumentPosition = [
        'preg_match' => 0,
        'preg_replace_callback_array' => 0,
        'preg_replace_callback' => 0,
        'preg_replace' => 0,
        'preg_match_all' => 0,
        'preg_split' => 0,
        'preg_grep' => 0,
    ];

    /**
     * @var int[][]
     */
    private $staticMethodsWithPatternsToArgumentPosition = [
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
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(
        NodeTypeResolver $nodeTypeResolver,
        NameResolver $nameResolver,
        ParsedNodesByType $parsedNodesByType,
        BetterNodeFinder $betterNodeFinder,
        BetterStandardPrinter $betterStandardPrinter
    ) {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nameResolver = $nameResolver;
        $this->parsedNodesByType = $parsedNodesByType;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
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
        foreach ($this->functionsWithPatternsToArgumentPosition as $functionName => $argumentPosition) {
            if (! $this->nameResolver->isName($funcCall, $functionName)) {
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
        foreach ($this->staticMethodsWithPatternsToArgumentPosition as $type => $methodNamesToArgumentPosition) {
            if (! $this->nodeTypeResolver->isObjectType($staticCall->class, $type)) {
                continue;
            }

            foreach ($methodNamesToArgumentPosition as $methodName => $argumentPosition) {
                if (! $this->nameResolver->isName($staticCall, $methodName)) {
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
        $methodNode = $variable->getAttribute(AttributeKey::METHOD_NODE);
        if ($methodNode === null) {
            return [];
        }

        return $this->betterNodeFinder->find([$methodNode], function (Node $node) use ($variable): ?Assign {
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
        $classConstNode = $this->parsedNodesByType->findClassConstantByClassConstFetch($classConstFetch);
        if ($classConstNode === null) {
            return [];
        }

        if ($classConstNode->consts[0]->value instanceof String_) {
            return [$classConstNode->consts[0]->value];
        }

        return [];
    }
}
