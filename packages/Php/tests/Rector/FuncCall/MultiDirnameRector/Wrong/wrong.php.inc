<?php declare(strict_types=1);

dirname(dirname($path));

new dirname(dirname(dirname($path)));


// untouched
dirname(dirname($path, $level));

dirname("foo/".dirname($path));

dirname(dirname($path).$foo);

foo\dirname(dirname($path));
