<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector\Fixture;

class OnClosure
{
    public function run()
    {
        $foo = array_filter(
            $list,
            function (\Throwable $fieldArgValue) {
              return is_null($fieldArgValue);
            }
          );
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeThrowableTypeDeclarationRector\Fixture;

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
