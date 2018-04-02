<?php declare(strict_types=1);

namespace Rector\Tests\RectorDefinition;

use PHPUnit\Framework\TestCase;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

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
}
