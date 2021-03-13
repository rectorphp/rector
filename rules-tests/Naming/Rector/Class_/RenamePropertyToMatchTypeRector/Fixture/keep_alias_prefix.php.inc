<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

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
