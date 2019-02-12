<?php declare(strict_types=1);

namespace Rector\Tests\RectorDefinition;

use PHPUnit\Framework\TestCase;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use stdClass;
use TypeError;

final class RectorDefinitionTest extends TestCase
{
    public function test(): void
    {
        $rectorDefinition = new RectorDefinition('Some description', [
            new CodeSample('Code before', 'Code after'),
        ]);

        $this->assertSame('Some description', $rectorDefinition->getDescription());

        $codeSamples = $rectorDefinition->getCodeSamples();
        $this->assertCount(1, $codeSamples);

        $codeSample = $codeSamples[0];
        $this->assertSame('Code before', $codeSample->getCodeBefore());
        $this->assertSame('Code after', $codeSample->getCodeAfter());
    }

    public function testInvalidCodeSamplesType(): void
    {
        $this->expectException(TypeError::class);
        new RectorDefinition('Some description', [new stdClass()]);
    }

    public function testEmptyCodeSamples(): void
    {
        $rectorDefinition = new RectorDefinition('Some description');
        $this->assertCount(0, $rectorDefinition->getCodeSamples());
    }
}
