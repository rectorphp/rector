<?php

namespace Rector\Tests\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector\Fixture;

class NoReturnToReturnVoid
{
   public function run(array $name = [])
   {
        call_user_func_array(function () use ($name) {

        }, []);
   }

   public function run2(array $name = [])
   {
        call_user_func_array(function () use ($name) {
            return;
        }, []);
   }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector\Fixture;

class NoReturnToReturnVoid
{
   public function run(array $name = [])
   {
        call_user_func_array(function () use ($name): void {

        }, []);
   }

   public function run2(array $name = [])
   {
        call_user_func_array(function () use ($name): void {
            return;
        }, []);
   }
}

?>
