<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\PropertyTypeResolver\Source;

final class MethodParamDocBlock
{
    /**
     * @var Html
     */
    public $htmlProperty;

    /**
     * @var ClassThatExtendsHtml
     */
    public $anotherHtmlProperty;
}
