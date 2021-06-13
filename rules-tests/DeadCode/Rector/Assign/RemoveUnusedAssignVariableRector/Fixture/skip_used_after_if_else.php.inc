<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\Assign\RemoveUnusedAssignVariableRector\Fixture;

class SkipUsedAfterIfElse {
  function test() {
    if ( true ) {
      $test1 = '123';
      $test2 = '123';
    } else {
      $test1 = 'abc';
      $test2 = 'abc';
    }

    echo $test1 . $test2;
  }
}

?>