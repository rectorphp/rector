<?php

namespace Rector\Php71\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

use SimpleXMLElement;

class SkipSimpleXmlElement
{
    public function run(SimpleXMLElement $simpleXMLElement)
    {
        return count($simpleXMLElement);
    }
}
