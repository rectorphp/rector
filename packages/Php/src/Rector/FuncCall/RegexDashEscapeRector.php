<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
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
     * Matches:
     * [\w-\d]
     *
     * Skips:
     * [-
     * [\-
     * a-z
     * A-Z
     * 0-9
     * (-
     * -]
     * -)
     * @var string
     * @see https://regex101.com/r/6zvPjH/1/
     */
    private const PATTERN_DASH_NOT_AROUND_BRACKETS = '#(?<!\[|\(|A|a|0|\\\\)-(?!\]|\)|A|a|0)#';

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

    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Escape - in some cases', [
            new CodeSample(
                <<<'CODE_SAMPLE'
preg_match("#[\w()-]#", 'some text'); // ok
preg_match("#[-\w()]#", 'some text'); // ok
preg_match("#[\w-()]#", 'some text'); // NOPE!
preg_match("#[\w(-)]#", 'some text'); // ok
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
preg_match("#[\w()-]#", 'some text'); // ok
preg_match("#[-\w()]#", 'some text'); // ok
preg_match("#[\w\-()]#", 'some text'); // NOPE!
preg_match("#[\w(-)]#", 'some text'); // ok
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
        // pattern can be defined in property or contant above
        if ($expr instanceof Variable) {
            $assignNodes = $this->findAssigners($expr);

            foreach ($assignNodes as $assignNode) {
                if ($assignNode->expr instanceof String_) {
                    $this->escapeStringNode($assignNode->expr);
                }
            }
        }

        if ($expr instanceof String_) {
            $this->escapeStringNode($expr);
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

    private function escapeStringNode(String_ $stringNode): void
    {
        $stringValue = $stringNode->value;

        if (! Strings::match($stringValue, self::PATTERN_DASH_NOT_AROUND_BRACKETS)) {
            return;
        }

        $stringNode->value = Strings::replace($stringValue, self::PATTERN_DASH_NOT_AROUND_BRACKETS, '\-');
        // helped needed to skip re-escaping regular expression
        $stringNode->setAttribute('is_regular_pattern', true);
    }
}
