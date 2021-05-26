<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

class OnClosure
{
    public function run()
    {
        $foo = array_filter(
            $list,
            function (mixed $fieldArgValue) {
              return is_null($fieldArgValue);
            }
          );
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\FunctionLike\DowngradeMixedTypeDeclarationRector\Fixture;

class OnClosure
{
    public function run()
    {
        $foo = array_filter(
            $list,
            function ($fieldArgValue) {
              return is_null($fieldArgValue);
            }
          );
    }
}

?>
