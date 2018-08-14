<?php declare(strict_types=1);

namespace Rector\Tests\NodeValueResolver;

use PhpParser\BuilderFactory;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node\Name;
use PHPUnit\Framework\TestCase;
use Rector\NodeTypeResolver\Node\Attribute as RectorAttribute;
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
        $classConstFetchNode = (new BuilderFactory())->classConstFetch('SomeClass', 'SOME_CONSTANT');
        $classConstFetchNode->class->setAttribute(RectorAttribute::RESOLVED_NAME, new Name('SomeClassResolveName'));

        $this->assertSame(
            'SomeClassResolveName::SOME_CONSTANT',
            $this->constExprEvaluator->evaluateDirectly($classConstFetchNode)
        );
    }
}
