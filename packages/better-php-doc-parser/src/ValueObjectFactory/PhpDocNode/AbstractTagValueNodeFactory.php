<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode;

use Rector\BetterPhpDocParser\Printer\ArrayPartPhpDocTagPrinter;
use Rector\BetterPhpDocParser\Printer\TagValueNodePrinter;

abstract class AbstractTagValueNodeFactory
{
    /**
     * @var ArrayPartPhpDocTagPrinter
     */
    protected $arrayPartPhpDocTagPrinter;

    /**
     * @var TagValueNodePrinter
     */
    protected $tagValueNodePrinter;

    public function __construct(
        ArrayPartPhpDocTagPrinter $arrayPartPhpDocTagPrinter,
        TagValueNodePrinter $tagValueNodePrinter
    ) {
        $this->arrayPartPhpDocTagPrinter = $arrayPartPhpDocTagPrinter;
        $this->tagValueNodePrinter = $tagValueNodePrinter;
    }
}
