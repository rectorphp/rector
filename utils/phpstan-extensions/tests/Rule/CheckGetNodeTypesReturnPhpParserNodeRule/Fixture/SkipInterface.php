<?php


namespace Rector\PHPStanExtensions\Tests\Rule\CheckGetNodeTypesReturnPhpParserNodeRule\Fixture;


interface SkipInterface
{
    public function getNodeTypes(): array;
}
