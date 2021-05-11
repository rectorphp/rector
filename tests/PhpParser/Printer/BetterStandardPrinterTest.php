<?php

declare(strict_types=1);

namespace Rector\Core\Tests\PhpParser\Printer;

use Iterator;
use PhpParser\Comment;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;

final class BetterStandardPrinterTest extends AbstractTestCase
{
    private BetterStandardPrinter $betterStandardPrinter;

    protected function setUp(): void
    {
        $this->boot();
        $this->betterStandardPrinter = $this->getService(BetterStandardPrinter::class);
    }

    public function testAddingCommentOnSomeNodesFail(): void
    {
        $methodCall = new MethodCall(new Variable('this'), 'run');

        // cannot be on MethodCall, must be Expression
        $methodCallExpression = new Expression($methodCall);
        $methodCallExpression->setAttribute(AttributeKey::COMMENTS, [new Comment('// todo: fix')]);

        $methodBuilder = new MethodBuilder('run');
        $methodBuilder->addStmt($methodCallExpression);

        $classMethod = $methodBuilder->getNode();

        $printed = $this->betterStandardPrinter->print($classMethod) . PHP_EOL;
        $this->assertStringEqualsFile(
            __DIR__ . '/Source/expected_code_with_non_stmt_placed_nested_comment.php.inc',
            $printed
        );
    }

    public function testStringWithAddedComment(): void
    {
        $string = new String_('hey');
        $string->setAttribute(AttributeKey::COMMENTS, [new Comment('// todo: fix')]);

        $printed = $this->betterStandardPrinter->print($string) . PHP_EOL;
        $this->assertStringEqualsFile(__DIR__ . '/Source/expected_code_with_comment.php.inc', $printed);
    }

    /**
     * @dataProvider provideDataForDoubleSlashEscaping()
     */
    public function testDoubleSlashEscaping(string $content, string $expectedOutput): void
    {
        $printed = $this->betterStandardPrinter->print(new String_($content));
        $this->assertSame($expectedOutput, $printed);
    }

    /**
     * @return Iterator<string[]>
     */
    public function provideDataForDoubleSlashEscaping(): Iterator
    {
        yield ['Vendor\Name', "'Vendor\Name'"];
        yield ['Vendor\\', "'Vendor\\\\'"];
        yield ["Vendor'Name", "'Vendor\'Name'"];
    }

    public function testYield(): void
    {
        $yield = new Yield_(new String_('value'));

        $printed = $this->betterStandardPrinter->print($yield);
        $this->assertSame("(yield 'value')", $printed);

        $printed = $this->betterStandardPrinter->print(new Yield_());
        $this->assertSame('yield', $printed);

        $expression = new Expression($yield);
        $yield->setAttribute(AttributeKey::PARENT_NODE, $expression);
        $printed = $this->betterStandardPrinter->print($expression);
        $this->assertSame("yield 'value';", $printed);
    }
}
