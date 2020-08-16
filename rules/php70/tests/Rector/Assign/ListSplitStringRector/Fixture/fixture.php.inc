<?php

namespace Rector\Php70\Tests\Rector\Assign\ListSplitStringRector\Fixture;

$string = 'abcde';
list($foo) = $string;

$array = ['abcde'];
list($bar) = $array;

list($foo) = 'abcde';

?>
-----
<?php

namespace Rector\Php70\Tests\Rector\Assign\ListSplitStringRector\Fixture;

$string = 'abcde';
list($foo) = str_split($string);

$array = ['abcde'];
list($bar) = $array;

list($foo) = str_split('abcde');

?>
