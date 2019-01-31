<?php declare(strict_types=1);

namespace Rector\Tests\PhpParser\Node;

use PhpParser\BuilderFactory;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPUnit\Framework\TestCase;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\ConstExprEvaluatorFactory;
use Rector\Tests\AbstractContainerAwareTestCase;

final class ConstExprEvaluatorFactoryTest extends AbstractContainerAwareTestCase
{
    /**
     * @var ConstExprEvaluator
     */
    private $constExprEvaluator;

    protected function setUp(): void
    {
        $this->constExprEvaluator = $this->container->get(ConstExprEvaluator::class);
    }

    public function test(): void
    {
        $classConstFetchNode = (new BuilderFactory())->classConstFetch('SomeClass', 'SOME_CONSTANT');
        $classConstFetchNode->class->setAttribute(Attribute::RESOLVED_NAME, new FullyQualified('SomeClassResolveName'));

        $this->assertSame(
            'SomeClassResolveName::SOME_CONSTANT',
            $this->constExprEvaluator->evaluateDirectly($classConstFetchNode)
        );
    }
}
