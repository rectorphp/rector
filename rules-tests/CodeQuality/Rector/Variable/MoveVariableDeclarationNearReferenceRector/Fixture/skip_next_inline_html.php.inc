<?php

namespace Rector\Tests\CodeQuality\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Fixture;

function test() {
  $title = 'abc';
  ?>
  <h1>
    <?php echo $title; ?>
  </h1>
  <?php
}

?>
