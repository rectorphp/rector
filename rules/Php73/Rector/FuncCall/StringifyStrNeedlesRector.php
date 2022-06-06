<?php

declare (strict_types=1);
namespace Rector\Php73\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\Encapsed;
use PHPStan\Type\StringType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php73\NodeTypeAnalyzer\NodeTypeAnalyzer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/deprecations_php_7_3#string_search_functions_with_integer_needle
 * @see \Rector\Tests\Php73\Rector\FuncCall\StringifyStrNeedlesRector\StringifyStrNeedlesRectorTest
 */
final class StringifyStrNeedlesRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var string[]
     */
    private const NEEDLE_STRING_SENSITIVE_FUNCTIONS = ['strpos', 'strrpos', 'stripos', 'strstr', 'stripos', 'strripos', 'strstr', 'strchr', 'strrchr', 'stristr'];
    /**
     * @readonly
     * @var \Rector\Php73\NodeTypeAnalyzer\NodeTypeAnalyzer
     */
    private $nodeTypeAnalyzer;
    public function __construct(\Rector\Php73\NodeTypeAnalyzer\NodeTypeAnalyzer $nodeTypeAnalyzer)
    {
        $this->nodeTypeAnalyzer = $nodeTypeAnalyzer;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::DEPRECATE_INT_IN_STR_NEEDLES;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Makes needles explicit strings', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$needle = 5;
$fivePosition = strpos('725', $needle);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$needle = 5;
$fivePosition = strpos('725', (string) $needle);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isNames($node, self::NEEDLE_STRING_SENSITIVE_FUNCTIONS)) {
            return null;
        }
        if (!isset($node->args[1])) {
            return null;
        }
        if (!$node->args[1] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        // is argument string?
        $needleArgValue = $node->args[1]->value;
        $needleType = $this->getType($needleArgValue);
        if ($needleType instanceof \PHPStan\Type\StringType) {
            return null;
        }
        if ($this->nodeTypeAnalyzer->isStringyType($needleType)) {
            return null;
        }
        if ($needleArgValue instanceof \PhpParser\Node\Expr\Cast\String_) {
            return null;
        }
        if ($needleArgValue instanceof \PhpParser\Node\Scalar\Encapsed) {
            return null;
        }
        $node->args[1]->value = new \PhpParser\Node\Expr\Cast\String_($node->args[1]->value);
        return $node;
    }
}
