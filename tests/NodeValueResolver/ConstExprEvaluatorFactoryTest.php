<?php declare(strict_types=1);

namespace Rector\Tests\NodeValueResolver;

use PhpParser\ConstExprEvaluator;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PHPUnit\Framework\TestCase;
use Rector\Node\Attribute;
use Rector\NodeValueResolver\ConstExprEvaluatorFactory;

final class ConstExprEvaluatorFactoryTest extends TestCase
{
    /**
     * @var ConstExprEvaluator
     */
    private $constExprEvaluator;

    protected function setUp(): void
    {
        $this->constExprEvaluator = (new ConstExprEvaluatorFactory())->create();
    }

    public function test(): void
    {
        $classConstFetchNode = new ClassConstFetch(new Class_('SomeClass'), 'SOME_CONSTANT');
        $classConstFetchNode->class->setAttribute(Attribute::RESOLVED_NAME, new Name('SomeClassResolveName'));

        $this->assertSame(
            'SomeClassResolveName::SOME_CONSTANT',
            $this->constExprEvaluator->evaluateDirectly($classConstFetchNode)
        );
    }
}
