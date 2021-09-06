<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

class SkipNoResultUsage
{
    /**
     * @var mixed
     */
    private $baseField;

    public function f($o)
    {
        $value = $this->baseField->getValue($o);

        if ($value === null) {
            return null;
        }

        return $this->baseField->select($value);
    }

}

?>
