<?php

declare(strict_types=1);

namespace Rector\Php73\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Php73\NodeTypeAnalyzer\NodeTypeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://wiki.php.net/rfc/deprecations_php_7_3#string_search_functions_with_integer_needle
 * @see \Rector\Php73\Tests\Rector\FuncCall\StringifyStrNeedlesRector\StringifyStrNeedlesRectorTest
 */
final class StringifyStrNeedlesRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const NEEDLE_STRING_SENSITIVE_FUNCTIONS = [
        'strpos',
        'strrpos',
        'stripos',
        'strstr',
        'stripos',
        'strripos',
        'strstr',
        'strchr',
        'strrchr',
        'stristr',
    ];

    /**
     * @var NodeTypeAnalyzer
     */
    private $nodeTypeAnalyzer;

    public function __construct(NodeTypeAnalyzer $nodeTypeAnalyzer)
    {
        $this->nodeTypeAnalyzer = $nodeTypeAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Makes needles explicit strings', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$needle = 5;
$fivePosition = strpos('725', $needle);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$needle = 5;
$fivePosition = strpos('725', (string) $needle);
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isNames($node, self::NEEDLE_STRING_SENSITIVE_FUNCTIONS)) {
            return null;
        }

        // is argument string?
        $needleArgNode = $node->args[1]->value;

        if ($this->nodeTypeAnalyzer->isStringTypeExpr($needleArgNode)) {
            return null;
        }

        if ($node->args[1]->value instanceof String_) {
            return null;
        }

        $node->args[1]->value = new String_($node->args[1]->value);

        return $node;
    }
}
