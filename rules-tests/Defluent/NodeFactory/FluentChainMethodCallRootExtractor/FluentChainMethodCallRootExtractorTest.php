<?php

declare(strict_types=1);

namespace Rector\Defluent\Tests\NodeFactory\FluentChainMethodCallRootExtractor;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallRootExtractor;
use Rector\Defluent\ValueObject\AssignAndRootExpr;
use Rector\Defluent\ValueObject\FluentCallsKind;
use Rector\Testing\TestingParser\TestingParser;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class FluentChainMethodCallRootExtractorTest extends AbstractKernelTestCase
{
    /**
     * @var FluentChainMethodCallRootExtractor
     */
    private $fluentChainMethodCallRootExtractor;

    /**
     * @var TestingParser
     */
    private $testingParser;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->fluentChainMethodCallRootExtractor = $this->getService(FluentChainMethodCallRootExtractor::class);
        $this->testingParser = $this->getService(TestingParser::class);
    }

    public function test(): void
    {
        $assignAndRootExpr = $this->parseFileAndCreateAssignAndRootExprForSure(
            __DIR__ . '/Fixture/skip_variable_double_method_call.php.inc'
        );

        $this->assertFalse($assignAndRootExpr->isFirstCallFactory());

        $this->assertNull($assignAndRootExpr->getSilentVariable());
    }

    public function testFactory(): void
    {
        $assignAndRootExpr = $this->parseFileAndCreateAssignAndRootExprForSure(
            __DIR__ . '/Fixture/skip_is_factory_variable_double_method_call.php.inc'
        );

        $this->assertTrue($assignAndRootExpr->isFirstCallFactory());
    }

    public function testNew(): void
    {
        $assignAndRootExpr = $this->parseFileAndCreateAssignAndRootExprForSure(
            __DIR__ . '/Fixture/skip_return_new_double_method_call.php.inc'
        );

        $this->assertFalse($assignAndRootExpr->isFirstCallFactory());

        /** @var Variable $silentVariable */
        $silentVariable = $assignAndRootExpr->getSilentVariable();
        $this->assertInstanceOf(Variable::class, $silentVariable);

        $this->assertIsString($silentVariable->name);
        $this->assertSame('someClassWithFluentMethods', $silentVariable->name);
    }

    public function testSingleMethodCallNull(): void
    {
        $assignAndRootExpr = $this->parseFileAndCreateAssignAndRootExpr(
            __DIR__ . '/Fixture/skip_non_fluent_nette_container_builder.php.inc'
        );

        $this->assertNull($assignAndRootExpr);
    }

    private function parseFileAndCreateAssignAndRootExprForSure(string $filePath): AssignAndRootExpr
    {
        $assignAndRootExpr = $this->parseFileAndCreateAssignAndRootExpr($filePath);
        $this->assertInstanceOf(AssignAndRootExpr::class, $assignAndRootExpr);

        /** @var AssignAndRootExpr $assignAndRootExpr */
        return $assignAndRootExpr;
    }

    private function parseFileAndCreateAssignAndRootExpr(string $filePath): ?AssignAndRootExpr
    {
        /** @var MethodCall[] $methodCalls */
        $methodCalls = $this->testingParser->parseFileToDecoratedNodesAndFindNodesByType(
            $filePath,
            MethodCall::class
        );

        return $this->fluentChainMethodCallRootExtractor->extractFromMethodCalls(
            $methodCalls,
            FluentCallsKind::NORMAL
        );
    }
}
