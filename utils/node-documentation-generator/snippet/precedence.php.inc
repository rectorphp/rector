<?php

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\TraitUseAdaptation\Precedence;

$firstTrait = new Name('SomeTrait');
$secondTrait = new Name('OverriddenTrait');

return new Precedence($firstTrait, 'methodName', [$secondTrait]);
