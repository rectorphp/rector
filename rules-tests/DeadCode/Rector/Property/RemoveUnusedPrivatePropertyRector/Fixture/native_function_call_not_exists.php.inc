<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class NativeFunctionCallNotExists
{
    private $resultID;
    private $rowOffset;
    public function getFieldCount(): int
	{
		return sqlsrv_num_fields($this->resultID);
	}
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class NativeFunctionCallNotExists
{
    private $resultID;
    public function getFieldCount(): int
	{
		return sqlsrv_num_fields($this->resultID);
	}
}

?>
