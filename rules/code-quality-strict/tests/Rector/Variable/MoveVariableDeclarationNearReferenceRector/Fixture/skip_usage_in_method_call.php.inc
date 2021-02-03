<?php

namespace Rector\CodeQualityStrict\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipUsageInMethodCall
{
    function myMethod()
    {
        $crawler = $kernelBrowser->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Hello World', $crawler->filter('h1, h2, h3')->text());
    }
}
?>
