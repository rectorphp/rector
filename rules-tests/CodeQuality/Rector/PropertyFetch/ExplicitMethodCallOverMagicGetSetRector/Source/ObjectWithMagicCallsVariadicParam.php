<?php

declare(strict_types=1);

namespace Rector\Tests\CodeQuality\Rector\PropertyFetch\ExplicitMethodCallOverMagicGetSetRector\Source;

use Nette\SmartObject;

final class ObjectWithMagicCallsVariadicParam
{
    // adds magic __get() and __set() methods
    use SmartObject;

    private $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName(...$params)
    {
        if (isset($params[1])) {
            $this->name = $params[0] . $params[1];
            return;
        }

        $this->name = current($params);
    }
}
