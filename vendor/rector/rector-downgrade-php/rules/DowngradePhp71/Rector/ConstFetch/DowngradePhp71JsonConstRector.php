<?php

declare (strict_types=1);
namespace Rector\DowngradePhp71\Rector\ConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp72\NodeManipulator\JsonConstCleaner;
use Rector\Enum\JsonConstant;
use Rector\NodeAnalyzer\DefineFuncCallAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/function.json-encode.php#refsect1-function.json-encode-changelog
 *
 * @see \Rector\Tests\DowngradePhp71\Rector\ConstFetch\DowngradePhp71JsonConstRector\DowngradePhp71JsonConstRectorTest
 */
final class DowngradePhp71JsonConstRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DowngradePhp72\NodeManipulator\JsonConstCleaner
     */
    private $jsonConstCleaner;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\DefineFuncCallAnalyzer
     */
    private $defineFuncCallAnalyzer;
    /**
     * @var string
     */
    private const PHP71_JSON_CONSTANT_IS_KNOWN = 'php71_json_constant_is_known';
    public function __construct(JsonConstCleaner $jsonConstCleaner, DefineFuncCallAnalyzer $defineFuncCallAnalyzer)
    {
        $this->jsonConstCleaner = $jsonConstCleaner;
        $this->defineFuncCallAnalyzer = $defineFuncCallAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove Json constant that available only in php 7.1', [new CodeSample(<<<'CODE_SAMPLE'
json_encode($content, JSON_UNESCAPED_LINE_TERMINATORS);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
json_encode($content, 0);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ConstFetch::class, BitwiseOr::class, If_::class];
    }
    /**
     * @param ConstFetch|BitwiseOr|If_ $node
     * @return \PhpParser\Node\Expr|\PhpParser\Node\Stmt\If_|null|int
     */
    public function refactor(Node $node)
    {
        if ($node instanceof If_) {
            return $this->refactorIf($node);
        }
        // skip as known
        if ((bool) $node->getAttribute(self::PHP71_JSON_CONSTANT_IS_KNOWN)) {
            return null;
        }
        return $this->jsonConstCleaner->clean($node, [JsonConstant::UNESCAPED_LINE_TERMINATORS]);
    }
    private function refactorIf(If_ $if) : ?If_
    {
        if (!$this->defineFuncCallAnalyzer->isDefinedWithConstants($if->cond, [JsonConstant::UNESCAPED_LINE_TERMINATORS])) {
            return null;
        }
        $this->traverseNodesWithCallable($if, static function (Node $node) {
            $node->setAttribute(self::PHP71_JSON_CONSTANT_IS_KNOWN, \true);
            return null;
        });
        return $if;
    }
}
