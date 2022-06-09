<?php

namespace Rector\Tests\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class UseIncrement
{
    private array $_quoteIdentifierCallbacks = array();
    private int $_qiCallbacksCount = 0;

    public function getQuoteIdentifierCallback($adapter)
    {
        $callback = create_function('$v', 'return Mage::helper(\'customgrid/collection\')->callQuoteIdentifier($v, '.++$this->_qiCallbacksCount.');');
    }

}

?>
-----
<?php

namespace Rector\Tests\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class UseIncrement
{
    private array $_quoteIdentifierCallbacks = array();
    private int $_qiCallbacksCount = 0;

    public function getQuoteIdentifierCallback($adapter)
    {
        $callback = function ($v) {
            return Mage::helper('customgrid/collection')->callQuoteIdentifier($v, ++$this->_qiCallbacksCount);
        };
    }

}

?>
