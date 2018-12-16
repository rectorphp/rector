<?php declare(strict_types=1);

namespace Rector\Php\Rector\FuncCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
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
     * [a-z
     * [\w-\d]
     *
     * Skips:
     * [-
     * (-
     * -]
     * -)
     * @var string
     * @see https://regex101.com/r/6zvPjH/1/
     */
    private const PATTERN_DASH_NOT_AROUND_BRACKETS = '#(?<!\[|\()-(?!\]|\))#';

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
        if (! $this->isStringType($node->args[$argumentPosition]->value)) {
            return;
        }

        $node->args[$argumentPosition]->value = $this->escapeDashInPattern($node->args[$argumentPosition]->value);
    }

    private function escapeDashInPattern(Expr $value): Expr
    {
        if ($value instanceof String_) {
            $stringValue = $value->value;

            if (! Strings::match($stringValue, self::PATTERN_DASH_NOT_AROUND_BRACKETS)) {
                return $value;
            }

            $value->value = Strings::replace($stringValue, self::PATTERN_DASH_NOT_AROUND_BRACKETS, '\-');
            // helped needed to skip re-escaping regular expression
            $value->setAttribute('is_regular_pattern', true);
        }

        // @todo constants
        // @todo properties above

        return $value;
    }
}
