<?php

declare(strict_types=1);

namespace Rector\Tests\Exclusion;

use Mockery;
use PhpParser\Node;
use PHPUnit\Framework\TestCase;
use Rector\Contract\Exclusion\ExclusionCheckInterface;
use Rector\Exclusion\ExclusionManager;
use Rector\Rector\AbstractRector;

final class ExclusionManagerTest extends TestCase
{
    /**
     * @var AbstractRector
     */
    private $sampleRector;

    /**
     * @var Node
     */
    private $sampleNode;

    protected function setUp(): void
    {
        $this->sampleRector = Mockery::mock(AbstractRector::class);
        $this->sampleNode = Mockery::mock(Node::class);
    }

    public function testisNodeSkippedByRector(): void
    {
        //poor mans dataprovider because mockery's times() assertions don't work properly with dataproviders

        $this->doTest(false, []);
        $this->doTest(true, [$this->getCheckMock(true, true)]);
        $this->doTest(false, [$this->getCheckMock(false, true)]);

        //break on first
        $this->doTest(true, [$this->getCheckMock(true, true), $this->getCheckMock(false, false)]);

        //break on second
        $this->doTest(true, [$this->getCheckMock(false, true), $this->getCheckMock(true, true)]);
    }

    /**
     * @param bool  $expected
     * @param ExclusionCheckInterface[] $exclusionChecks
     */
    public function doTest(bool $expected, array $exclusionChecks): void
    {
        $manager = new ExclusionManager($exclusionChecks);
        $shouldExclude = $manager->isNodeSkippedByRector($this->sampleRector, $this->sampleNode);

        self::assertSame($expected, $shouldExclude);
        Mockery::close();
    }

    /**
     * @return ExclusionCheckInterface
     */
    private function getCheckMock(bool $return, bool $shouldBeCalled): ExclusionCheckInterface
    {
        $exclusionCheck = Mockery::mock(ExclusionCheckInterface::class);
        $exclusionCheck->shouldReceive('isNodeSkippedByRector')
            ->times($shouldBeCalled ? 1 : 0)
            ->andReturn($return);
        return $exclusionCheck;
    }
}
