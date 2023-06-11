<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PHPStan\Type\BooleanType;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradeJsonDecodeNullAssociativeArgRector\DowngradeJsonDecodeNullAssociativeArgRectorTest
 *
 * @changelog https://3v4l.org/b1mA6
 */
final class DowngradeJsonDecodeNullAssociativeArgRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(ArgsAnalyzer $argsAnalyzer)
    {
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Downgrade json_decode() with null associative argument function', [new CodeSample(<<<'CODE_SAMPLE'
function exactlyNull(string $json)
{
    $value = json_decode($json, null);
}

function possiblyNull(string $json, ?bool $associative)
{
    $value = json_decode($json, $associative);
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function exactlyNull(string $json)
{
    $value = json_decode($json, true);
}

function possiblyNull(string $json, ?bool $associative)
{
    $value = json_decode($json, $associative === null ?: $associative);
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'json_decode')) {
            return null;
        }
        $args = $node->getArgs();
        if ($this->argsAnalyzer->hasNamedArg($args)) {
            return null;
        }
        if (!isset($args[1])) {
            return null;
        }
        $associativeValue = $args[1]->value;
        // already converted
        if ($associativeValue instanceof Ternary && !$associativeValue->if instanceof Expr) {
            return null;
        }
        $associativeValueType = $this->nodeTypeResolver->getType($associativeValue);
        if ($associativeValueType instanceof BooleanType) {
            return null;
        }
        if ($associativeValue instanceof ConstFetch && $this->valueResolver->isNull($associativeValue)) {
            $args[1]->value = $this->nodeFactory->createTrue();
            return $node;
        }
        // add conditional ternary
        $nullIdentical = new Identical($associativeValue, $this->nodeFactory->createNull());
        $args[1]->value = new Ternary($nullIdentical, null, $associativeValue);
        return $node;
    }
}
