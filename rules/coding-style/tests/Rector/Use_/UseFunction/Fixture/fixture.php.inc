<?php

use function Deployer\set;

set('my_key', 'my_value');

?>
-----
<?php

use function Deployer\set;

set('my_key', 'my_value');
set('my_key_2', 'my_value_2');

?>
