<?php

namespace Rector\CodeQualityStrict\Tests\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

class SkipUsageInStaticCall
{
    function myMethod()
    {
        $crawler = $kernelBrowser->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Hello World', $crawler->filter('h1, h2, h3')->text());
    }
}
?>
