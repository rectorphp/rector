<?php declare(strict_types=1);

$d = $e ?? 'e';

$dd = $ee ?? 'ee';

$i = $this->${'a'}[0]->$$b[1][2]::$c[3][4][5]->xxx->{" $d"} ?? 0;

$j = $this->${'a'}[0]->$$b[1][2]::$c[3][4][5]->xxx->{" $d"} ?? false;

$k = $this
    ->${'a'}[0]
    ->$$b[1][2]
    ::$c[3][4][5]
    ->{" $d"} ?? true;

$l = \Whatever\Something::$anything ?? 1;

$m = $object->anything ?? 0;

$n = ($something ?? false);

$o[$something ?? true] = true;

$p = doSomething()() ?? false;
