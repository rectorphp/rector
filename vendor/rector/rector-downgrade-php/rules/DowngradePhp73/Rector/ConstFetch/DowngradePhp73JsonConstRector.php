<?php

declare (strict_types=1);
namespace Rector\DowngradePhp73\Rector\ConstFetch;

use RectorPrefix202306\Nette\Utils\Json;
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
 * @see \Rector\Tests\DowngradePhp73\Rector\ConstFetch\DowngradePhp73JsonConstRector\DowngradePhp73JsonConstRectorTest
 */
final class DowngradePhp73JsonConstRector extends AbstractRector
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
    private const PHP73_JSON_CONSTANT_IS_KNOWN = 'php73_json_constant_is_known';
    public function __construct(JsonConstCleaner $jsonConstCleaner, DefineFuncCallAnalyzer $defineFuncCallAnalyzer)
    {
        $this->jsonConstCleaner = $jsonConstCleaner;
        $this->defineFuncCallAnalyzer = $defineFuncCallAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove Json constant that available only in php 7.3', [new CodeSample(<<<'CODE_SAMPLE'
json_encode($content, JSON_THROW_ON_ERROR);
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
     * @return int|null|\PhpParser\Node\Expr|\PhpParser\Node\Stmt\If_
     */
    public function refactor(Node $node)
    {
        if ($node instanceof If_) {
            return $this->refactorIf($node);
        }
        // skip as known
        if ((bool) $node->getAttribute(self::PHP73_JSON_CONSTANT_IS_KNOWN)) {
            return null;
        }
        return $this->jsonConstCleaner->clean($node, [JsonConstant::THROW_ON_ERROR]);
    }
    private function refactorIf(If_ $if) : ?If_
    {
        if (!$this->defineFuncCallAnalyzer->isDefinedWithConstants($if->cond, [JsonConstant::THROW_ON_ERROR])) {
            return null;
        }
        $this->traverseNodesWithCallable($if, static function (Node $node) {
            $node->setAttribute(self::PHP73_JSON_CONSTANT_IS_KNOWN, \true);
            return null;
        });
        return $if;
    }
}
