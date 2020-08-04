<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use PhpParser\ParserFactory as NikicParserFactory;

final class KeepAliasPrefix
{
    /**
     * @var NikicParserFactory
     */
    private $nikicParserFactory;

    public function __construct(NikicParserFactory $nikicParserFactory)
    {
        $this->nikicParserFactory = $nikicParserFactory;
    }
}
