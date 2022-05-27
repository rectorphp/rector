<?php

declare (strict_types=1);
namespace Rector\DowngradePhp72\Rector\ConstFetch;

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
 * @see \Rector\Tests\DowngradePhp72\Rector\ConstFetch\DowngradePhp72JsonConstRector\DowngradePhp72JsonConstRectorTest
 */
final class DowngradePhp72JsonConstRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string[]
     */
    private const CONSTANTS = ['JSON_INVALID_UTF8_IGNORE', 'JSON_INVALID_UTF8_SUBSTITUTE'];
    /**
     * @readonly
     * @var \Rector\DowngradePhp72\NodeManipulator\JsonConstCleaner
     */
    private $jsonConstCleaner;
    public function __construct(\Rector\DowngradePhp72\NodeManipulator\JsonConstCleaner $jsonConstCleaner)
    {
        $this->jsonConstCleaner = $jsonConstCleaner;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove Json constant that available only in php 7.2', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\ConstFetch::class, \PhpParser\Node\Expr\BinaryOp\BitwiseOr::class];
    }
    /**
     * @param ConstFetch|BitwiseOr $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        return $this->jsonConstCleaner->clean($node, self::CONSTANTS);
    }
}
