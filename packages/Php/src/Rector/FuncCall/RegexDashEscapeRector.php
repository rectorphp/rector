<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\NodeTypeResolver\Application\ConstantNodeCollector;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/dRG8U
 */
final class RegexDashEscapeRector extends AbstractRector
{
    /**
     * @var string
     */
    private const LEFT_HAND_UNESCAPED_DASH_PATTERN = '#(\\\\(w|s|d))-(?!\])#i';

    /**
     * @var string
     * @see https://regex101.com/r/TBVme9/1
     */
    private const RIGHT_HAND_UNESCAPED_DASH_PATTERN = '#(?<!\[)-\\\\(w|s|d)#i';

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
        'Nette\Utils\Strings' => [
            'match' => 1,
            'matchAll' => 1,
            'replace' => 1,
            'split' => 1,
        ],
    ];

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var ConstantNodeCollector
     */
    private $constantNodeCollector;

    public function __construct(BetterNodeFinder $betterNodeFinder, ConstantNodeCollector $constantNodeCollector)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->constantNodeCollector = $constantNodeCollector;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Escape - in some cases', [
            new CodeSample(
                <<<'CODE_SAMPLE'
preg_match("#[\w-()]#", 'some text');
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
preg_match("#[\w\-()]#", 'some text');
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, StaticCall::class];
    }

    /**
     * @param FuncCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof FuncCall) {
            $this->processFuncCall($node);
        }

        if ($node instanceof StaticCall) {
            $this->processStaticCall($node);
        }

        return $node;
    }

    private function processFuncCall(FuncCall $funcCallNode): void
    {
        foreach ($this->functionsWithPatternsToArgumentPosition as $functionName => $argumentPosition) {
            if (! $this->isName($funcCallNode, $functionName)) {
                return;
            }

            $this->processArgumentPosition($funcCallNode, $argumentPosition);
        }
    }

    private function processStaticCall(StaticCall $staticCallNode): void
    {
        foreach ($this->staticMethodsWithPatternsToArgumentPosition as $type => $methodNamesToArgumentPosition) {
            if (! $this->isType($staticCallNode, $type)) {
                continue;
            }

            foreach ($methodNamesToArgumentPosition as $methodName => $argumentPosition) {
                if (! $this->isName($staticCallNode, $methodName)) {
                    continue;
                }

                $this->processArgumentPosition($staticCallNode, $argumentPosition);
            }
        }
    }

    /**
     * @param StaticCall|FuncCall $node
     */
    private function processArgumentPosition(Node $node, int $argumentPosition): void
    {
        $valueNode = $node->args[$argumentPosition]->value;
        if (! $this->isStringyType($valueNode)) {
            return;
        }

        $this->escapeDashInPattern($valueNode);
    }

    private function escapeDashInPattern(Expr $expr): void
    {
        if ($expr instanceof ClassConstFetch) {
            $this->processClassConstFetch($expr);
        }

        // pattern can be defined in property or contant above
        if ($expr instanceof Variable) {
            $this->processVariable($expr);
        }

        if ($expr instanceof String_) {
            $this->escapeStringNode($expr);
        }
    }

    private function processClassConstFetch(Expr $expr): void
    {
        $className = (string) $expr->getAttribute(Attribute::CLASS_NAME);
        $constantName = $this->getName($expr);
        if ($constantName === null) {
            return;
        }

        $classConstNode = $this->constantNodeCollector->findConstant($constantName, $className);
        if ($classConstNode) {
            if ($classConstNode->consts[0]->value instanceof String_) {
                /** @var String_ $stringNode */
                $stringNode = $classConstNode->consts[0]->value;
                $this->escapeStringNode($stringNode);
            }
        }
    }

    private function processVariable(Expr $expr): void
    {
        $assignNodes = $this->findAssigners($expr);

        foreach ($assignNodes as $assignNode) {
            if ($assignNode->expr instanceof String_) {
                $this->escapeStringNode($assignNode->expr);
            }
        }
    }

    private function escapeStringNode(String_ $stringNode): void
    {
        $stringValue = $stringNode->value;

        if (Strings::match($stringValue, self::LEFT_HAND_UNESCAPED_DASH_PATTERN)) {
            $stringNode->value = Strings::replace($stringValue, self::LEFT_HAND_UNESCAPED_DASH_PATTERN, '$1\-');
            // helped needed to skip re-escaping regular expression
            $stringNode->setAttribute('is_regular_pattern', true);
            return;
        }

        if (Strings::match($stringValue, self::RIGHT_HAND_UNESCAPED_DASH_PATTERN)) {
            $stringNode->value = Strings::replace($stringValue, self::RIGHT_HAND_UNESCAPED_DASH_PATTERN, '\-$2');
            // helped needed to skip re-escaping regular expression
            $stringNode->setAttribute('is_regular_pattern', true);
        }
    }

    /**
     * @return Assign[]
     */
    private function findAssigners(Node $variableNode): array
    {
        $methodNode = $variableNode->getAttribute(Attribute::METHOD_NODE);

        /** @var Assign[] $assignNode */
        return $this->betterNodeFinder->find([$methodNode], function (Node $node) use ($variableNode) {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $this->areNodesEqual($node->var, $variableNode)) {
                return null;
            }

            return $node;
        });
    }
}
