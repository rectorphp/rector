<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class rex_var_link extends rex_var
{
    protected function getOutput()
    {
        if ($this->hasArg('widget') && $this->getArg('widget')) {

        } else {
            if ($value && $this->hasArg('output') && 'id' != $this->getArg('output')) {
                $value = rex_getUrl($value);
            }
        }
        return $value;
    }
}

abstract class rex_var {
    /**
     * Returns the output.
     *
     * @return false|string
     */
    abstract protected function getOutput();
}
?>
-----
<?php

namespace Rector\EarlyReturn\Tests\Rector\If_\ChangeAndIfToEarlyReturnRector\Fixture;

class rex_var_link extends rex_var
{
    protected function getOutput()
    {
        if ($this->hasArg('widget') && $this->getArg('widget')) {

        } else {
            if (!$value) {
                return $value;
            }
            if (!$this->hasArg('output')) {
                return $value;
            }
            if ('id' == $this->getArg('output')) {
                return $value;
            }
            $value = rex_getUrl($value);
         }
        return $value;
    }
}

abstract class rex_var {
    /**
     * Returns the output.
     *
     * @return false|string
     */
    abstract protected function getOutput();
}
?>
