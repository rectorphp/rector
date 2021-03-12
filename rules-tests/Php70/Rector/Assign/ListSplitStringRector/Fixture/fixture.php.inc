<?php

namespace Rector\Tests\Php70\Rector\Assign\ListSplitStringRector\Fixture;

$string = 'abcde';
list($foo) = $string;

$array = ['abcde'];
list($bar) = $array;

list($foo) = 'abcde';

?>
-----
<?php

namespace Rector\Tests\Php70\Rector\Assign\ListSplitStringRector\Fixture;

$string = 'abcde';
list($foo) = str_split($string);

$array = ['abcde'];
list($bar) = $array;

list($foo) = str_split('abcde');

?>
