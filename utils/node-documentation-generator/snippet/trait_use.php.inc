<?php

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\TraitUse;

return new TraitUse([new FullyQualified('TraitName')]);
