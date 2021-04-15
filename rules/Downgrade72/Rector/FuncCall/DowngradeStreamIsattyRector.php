<?php

declare(strict_types=1);

namespace Rector\Downgrade72\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BitwiseAnd;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\ErrorSuppress;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Parser\InlineCodeParser;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://github.com/symfony/polyfill/commit/cc2bf55accd32b989348e2039e8c91cde46aebed
 *
 * @see \Rector\Tests\Downgrade72\Rector\FuncCall\DowngradeStreamIsattyRector\DowngradeStreamIsattyRectorTest
 */
final class DowngradeStreamIsattyRector extends AbstractRector
{
    /**
     * @var string
     */
    private const STAT = 'stat';

    /**
     * @var InlineCodeParser
     */
    private $inlineCodeParser;

    public function __construct(InlineCodeParser $inlineCodeParser)
    {
        $this->inlineCodeParser = $inlineCodeParser;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Downgrade stream_isatty() function', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($stream)
    {
        $isStream = stream_isatty($stream);
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($stream)
    {
        if ('\\' === \DIRECTORY_SEPARATOR)
            $stat = @fstat($stream);
            // Check if formatted mode is S_IFCHR
            $isStream = $stat ? 0020000 === ($stat['mode'] & 0170000) : false;
        } else {
            $isStream = @posix_isatty($stream)
        }
    }
}
CODE_SAMPLE

            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'stream_isatty')) {
            return null;
        }

        $function = $this->createClosure();
        $assign = new Assign(new Variable('streamIsatty'), $function);

        $this->addNodeBeforeNode($assign, $node);
        return new FuncCall(new Variable('streamIsatty'), $node->args);
    }

    private function createIf(Expr $expr): If_
    {
        $constFetch = new ConstFetch(new FullyQualified('DIRECTORY_SEPARATOR'));
        $identical = new Identical(new String_('\\'), $constFetch);

        $if = new If_($identical);
        $statAssign = new Assign(
            new Variable(self::STAT),
            new ErrorSuppress($this->nodeFactory->createFuncCall('fstat', [$expr]))
        );
        $if->stmts[] = new Expression($statAssign);

        $arrayDimFetch = new ArrayDimFetch(new Variable(self::STAT), new String_('mode'));
        $bitwiseAnd = new BitwiseAnd(
            $arrayDimFetch,
            new LNumber(0170000, [
                AttributeKey::KIND => LNumber::KIND_OCT,
            ])
        );

        $identical = new Identical(new LNumber(020000, [
            AttributeKey::KIND => LNumber::KIND_OCT,
        ]), $bitwiseAnd);

        $ternary = new Ternary(new Variable(self::STAT), $identical, $this->nodeFactory->createFalse());
        $if->stmts[] = new Return_($ternary);

        return $if;
    }

    private function createClosure(): Closure
    {
        $stmts = $this->inlineCodeParser->parse(__DIR__ . '/../../snippet/isatty_closure.php.inc');

        /** @var Expression $expression */
        $expression = $stmts[0];

        return $expression->expr;
    }
}
