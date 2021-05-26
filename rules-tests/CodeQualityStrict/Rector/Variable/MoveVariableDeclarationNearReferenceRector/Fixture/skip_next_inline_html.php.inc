<?php

namespace Rector\Tests\CodeQualityStrict\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

function test() {
  $title = 'abc';
  ?>
  <h1>
    <?php echo $title; ?>
  </h1>
  <?php
}

?>
