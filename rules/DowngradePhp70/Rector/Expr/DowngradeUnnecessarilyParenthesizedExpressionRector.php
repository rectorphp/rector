<?php

declare(strict_types=1);

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
final class DowngradeUnnecessarilyParenthesizedExpressionRector extends AbstractRector
{
    /**
     * @var array<class-string<Expr>>
     */
    private const PARENTHESIZABLE_NODES = [
        ArrayDimFetch::class,
        PropertyFetch::class,
        MethodCall::class,
        StaticPropertyFetch::class,
        StaticCall::class,
        FuncCall::class,
    ];

    public function __construct(
        private readonly WrappedInParenthesesAnalyzer $wrappedInParenthesesAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove parentheses around expressions allowed by Uniform variable syntax RFC where they are not necessary to prevent parse errors on PHP 5.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
($f)['foo'];
($f)->foo;
($f)->foo();
($f)::$foo;
($f)::foo();
($f)();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$f['foo'];
$f->foo;
$f->foo();
$f::$foo;
$f::foo();
$f();
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [
            // TODO: Make PHPStan rules allow Expr namespace for its subclasses.
            Expr::class,
        ];
    }

    /**
     * @param ArrayDimFetch|PropertyFetch|MethodCall|StaticPropertyFetch|StaticCall|FuncCall $node
     */
    public function refactor(Node $node): ?Expr
    {
        if (! in_array($node::class, self::PARENTHESIZABLE_NODES, true)) {
            return null;
        }

        $leftSubNode = $this->getLeftSubNode($node);
        if (! $leftSubNode instanceof Node) {
            return null;
        }

        if (! $this->wrappedInParenthesesAnalyzer->isParenthesized($this->file, $leftSubNode)) {
            return null;
        }

        // Parenthesization is not part of the AST and Rector only re-generates code for AST nodes that changed.
        // Let’s remove the original node reference forcing the re-generation of the corresponding code.
        // The code generator will only put parentheses where strictly necessary, which other rules should handle.
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);

        return $node;
    }

    private function getLeftSubNode(Node $node): ?Node
    {
        return match (true) {
            $node instanceof ArrayDimFetch => $node->var,
            $node instanceof PropertyFetch => $node->var,
            $node instanceof MethodCall => $node->var,
            $node instanceof StaticPropertyFetch => $node->class,
            $node instanceof StaticCall => $node->class,
            $node instanceof FuncCall => $node->name,
            default => null,
        };
    }
}
