<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Fixture;

final class StaticMethodProtected extends _Parent
{
	  static function X()
    {
    }
}

abstract class _Parent {
  protected static function X()
  {
  }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector\Fixture;

final class StaticMethodProtected extends _Parent
{
	  protected static function X()
    {
    }
}

abstract class _Parent {
  protected static function X()
  {
  }
}

?>
