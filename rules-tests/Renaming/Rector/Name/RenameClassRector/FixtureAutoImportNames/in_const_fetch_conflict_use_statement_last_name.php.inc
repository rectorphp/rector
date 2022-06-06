<?php

namespace Company\Commands\Queue;

use CompanyB\Queue;

$queue = \Queue::FOO;

?>
-----
<?php

namespace Company\Commands\Queue;

use CompanyB\Queue;

$queue = \Illuminate\Support\Facades\Queue::FOO;

?>
