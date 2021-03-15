<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

use SimpleXMLElement;

class SkipSimpleXmlElement
{
    public function run(SimpleXMLElement $simpleXMLElement)
    {
        return count($simpleXMLElement);
    }
}
