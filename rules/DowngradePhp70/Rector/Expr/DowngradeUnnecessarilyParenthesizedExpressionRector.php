<?php

declare (strict_types=1);
namespace Rector\DowngradePhp70\Rector\Expr;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\StaticPropertyFetch;
use Rector\Core\Rector\AbstractRector;
use Rector\DowngradePhp70\Tokenizer\WrappedInParenthesesAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/uniform_variable_syntax
 *
 * @see \Rector\Tests\DowngradePhp70\Rector\Expr\DowngradeUnnecessarilyParenthesizedExpressionRector\DowngradeUnnecessarilyParenthesizedExpressionRectorTest
 */
final class DowngradeUnnecessarilyParenthesizedExpressionRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<class-string<Expr>>
     */
    private const PARENTHESIZABLE_NODES = [\PhpParser\Node\Expr\ArrayDimFetch::class, \PhpParser\Node\Expr\PropertyFetch::class, \PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticPropertyFetch::class, \PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Expr\FuncCall::class];
    /**
     * @readonly
     * @var \Rector\DowngradePhp70\Tokenizer\WrappedInParenthesesAnalyzer
     */
    private $wrappedInParenthesesAnalyzer;
    public function __construct(\Rector\DowngradePhp70\Tokenizer\WrappedInParenthesesAnalyzer $wrappedInParenthesesAnalyzer)
    {
        $this->wrappedInParenthesesAnalyzer = $wrappedInParenthesesAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove parentheses around expressions allowed by Uniform variable syntax RFC where they are not necessary to prevent parse errors on PHP 5.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
($f)['foo'];
($f)->foo;
($f)->foo();
($f)::$foo;
($f)::foo();
($f)();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$f['foo'];
$f->foo;
$f->foo();
$f::$foo;
$f::foo();
$f();
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [
            // TODO: Make PHPStan rules allow Expr namespace for its subclasses.
            \PhpParser\Node\Expr::class,
        ];
    }
    /**
     * @param ArrayDimFetch|PropertyFetch|MethodCall|StaticPropertyFetch|StaticCall|FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node\Expr
    {
        if (!\in_array(\get_class($node), self::PARENTHESIZABLE_NODES, \true)) {
            return null;
        }
        $leftSubNode = $this->getLeftSubNode($node);
        if (!$leftSubNode instanceof \PhpParser\Node) {
            return null;
        }
        if (!$this->wrappedInParenthesesAnalyzer->isParenthesized($this->file, $leftSubNode)) {
            return null;
        }
        // Parenthesization is not part of the AST and Rector only re-generates code for AST nodes that changed.
        // Let’s remove the original node reference forcing the re-generation of the corresponding code.
        // The code generator will only put parentheses where strictly necessary, which other rules should handle.
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        return $node;
    }
    private function getLeftSubNode(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        switch (\true) {
            case $node instanceof \PhpParser\Node\Expr\ArrayDimFetch:
                return $node->var;
            case $node instanceof \PhpParser\Node\Expr\PropertyFetch:
                return $node->var;
            case $node instanceof \PhpParser\Node\Expr\MethodCall:
                return $node->var;
            case $node instanceof \PhpParser\Node\Expr\StaticPropertyFetch:
                return $node->class;
            case $node instanceof \PhpParser\Node\Expr\StaticCall:
                return $node->class;
            case $node instanceof \PhpParser\Node\Expr\FuncCall:
                return $node->name;
            default:
                return null;
        }
    }
}
