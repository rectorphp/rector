<?php

declare (strict_types=1);
namespace Rector\DowngradePhp71\Rector\ConstFetch;

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
 * @see \Rector\Tests\DowngradePhp71\Rector\ConstFetch\DowngradePhp71JsonConstRector\DowngradePhp71JsonConstRectorTest
 */
final class DowngradePhp71JsonConstRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const CONSTANTS = ['JSON_UNESCAPED_LINE_TERMINATORS'];
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
