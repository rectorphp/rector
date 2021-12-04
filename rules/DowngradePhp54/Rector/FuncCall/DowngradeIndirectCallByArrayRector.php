<?php

declare (strict_types=1);
namespace Rector\DowngradePhp54\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/indirect-method-call-by-array-var
 *
 * @see \Rector\Tests\DowngradePhp54\Rector\FuncCall\DowngradeIndirectCallByArrayRector\DowngradeIndirectCallByArrayRectorTest
 */
final class DowngradeIndirectCallByArrayRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer
     */
    private $stringTypeAnalyzer;
    public function __construct(\Rector\NodeTypeResolver\TypeAnalyzer\StringTypeAnalyzer $stringTypeAnalyzer)
    {
        $this->stringTypeAnalyzer = $stringTypeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Downgrade indirect method call by array variable', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class Hello {
    public static function world($x) {
        echo "Hello, $x\n";
    }
}

$func = array('Hello','world');
$func('you');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class Hello {
    public static function world($x) {
        echo "Hello, $x\n";
    }
}

$func = array('Hello','world');
call_user_func($func, 'you');
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
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node\Expr\FuncCall
    {
        if (!$node->name instanceof \PhpParser\Node\Expr\Variable) {
            return null;
        }
        if ($this->stringTypeAnalyzer->isStringOrUnionStringOnlyType($node->name)) {
            return null;
        }
        $args = \array_merge([new \PhpParser\Node\Arg($node->name)], $node->args);
        return new \PhpParser\Node\Expr\FuncCall(new \PhpParser\Node\Name('call_user_func'), $args);
    }
}
