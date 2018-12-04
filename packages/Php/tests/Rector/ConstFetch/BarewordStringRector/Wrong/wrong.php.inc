<?php

namespace Rector\Php\Tests\Rector\ConstFetch\BarewordStringRector\Wrong;

const THIS_EXISTS = 'yes';

is_bool(SOME_STRING);

is_bool(true);
is_bool(false);
is_bool(NULL);
is_bool(__DIR__);

is_bool(THIS_EXISTS);

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ConstFetch\BarewordStringRector\Wrong;

const THIS_EXISTS = 'yes';

is_bool('SOME_STRING');

is_bool(true);
is_bool(false);
is_bool(NULL);
is_bool(__DIR__);

is_bool(THIS_EXISTS);

?>
