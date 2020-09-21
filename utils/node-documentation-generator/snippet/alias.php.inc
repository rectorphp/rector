<?php

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\TraitUseAdaptation\Alias;

$traitFullyQualified = new FullyQualified('TraitName');

return new Alias($traitFullyQualified, 'method', Class_::MODIFIER_PUBLIC, 'aliasedMethod');
