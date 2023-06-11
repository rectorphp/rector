<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\Rector\ConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeTraverser;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp72\NodeManipulator\JsonConstCleaner;
use Rector\Enum\JsonConstant;
use Rector\NodeAnalyzer\DefineFuncCallAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/function.json-encode.php#refsect1-function.json-encode-changelog
 *
 * @see \Rector\Tests\DowngradePhp72\Rector\ConstFetch\DowngradePhp72JsonConstRector\DowngradePhp72JsonConstRectorTest
 */
final class DowngradePhp72JsonConstRector extends AbstractRector
{
    /**
     * @var \Rector\DowngradePhp72\NodeManipulator\JsonConstCleaner
     */
    private $jsonConstCleaner;
    /**
     * @var \Rector\NodeAnalyzer\DefineFuncCallAnalyzer
     */
    private $defineFuncCallAnalyzer;
    public function __construct(JsonConstCleaner $jsonConstCleaner, DefineFuncCallAnalyzer $defineFuncCallAnalyzer)
    {
        $this->jsonConstCleaner = $jsonConstCleaner;
        $this->defineFuncCallAnalyzer = $defineFuncCallAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove Json constant that available only in php 7.2', [new CodeSample(<<<'CODE_SAMPLE'
$inDecoder = new Decoder($connection, true, 512, \JSON_INVALID_UTF8_IGNORE);

$inDecoder = new Decoder($connection, true, 512, \JSON_INVALID_UTF8_SUBSTITUTE);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$inDecoder = new Decoder($connection, true, 512, 0);

$inDecoder = new Decoder($connection, true, 512, 0);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ConstFetch::class, BitwiseOr::class, FuncCall::class];
    }
    /**
     * @param ConstFetch|BitwiseOr|FuncCall $node
     * @return \PhpParser\Node\Expr|null|int
     */
    public function refactor(Node $node)
    {
        if ($node instanceof FuncCall) {
            if ($this->defineFuncCallAnalyzer->isDefinedWithConstants($node, [JsonConstant::INVALID_UTF8_IGNORE, JsonConstant::INVALID_UTF8_SUBSTITUTE])) {
                return NodeTraverser::STOP_TRAVERSAL;
            }
            return null;
        }
        return $this->jsonConstCleaner->clean($node, [JsonConstant::INVALID_UTF8_IGNORE, JsonConstant::INVALID_UTF8_SUBSTITUTE]);
    }
}
