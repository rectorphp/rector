<?php

declare (strict_types=1);
namespace Rector\DowngradePhp73\Rector\ConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ConstFetch;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp72\NodeManipulator\JsonConstCleaner;
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
     * @var string[]
     */
    private const CONSTANTS = ['JSON_THROW_ON_ERROR'];
    /**
     * @readonly
     * @var \Rector\DowngradePhp72\NodeManipulator\JsonConstCleaner
     */
    private $jsonConstCleaner;
    public function __construct(JsonConstCleaner $jsonConstCleaner)
    {
        $this->jsonConstCleaner = $jsonConstCleaner;
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
        return [ConstFetch::class, BitwiseOr::class];
    }
    /**
     * @param ConstFetch|BitwiseOr $node
     */
    public function refactor(Node $node) : ?Node
    {
        return $this->jsonConstCleaner->clean($node, self::CONSTANTS);
    }
}
