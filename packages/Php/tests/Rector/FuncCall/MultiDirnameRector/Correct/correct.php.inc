<?php declare(strict_types=1);

dirname($path, 2);

new dirname(dirname($path, 2));


// untouched
dirname(dirname($path, $level));

dirname("foo/".dirname($path));

dirname(dirname($path).$foo);

foo\dirname(dirname($path));
