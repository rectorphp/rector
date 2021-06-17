<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class KeepUnderscore
{
    public function run()
    {
        $customer_names = [];
        foreach ($customer_names as $customer_name) {
          echo $customer_name;
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector\Fixture;

class KeepUnderscore
{
    public function run()
    {
        $customer_names = [];
        foreach ($customer_names as $customer_name) {
          echo $customer_name;
        }
    }
}

?>
