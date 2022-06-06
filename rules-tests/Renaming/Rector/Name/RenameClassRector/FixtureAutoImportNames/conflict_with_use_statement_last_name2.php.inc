<?php

namespace Company\Commands\Queue;

use CompanyB\Queue;

$queue = app(\Queue::class);

?>
-----
<?php

namespace Company\Commands\Queue;

use CompanyB\Queue;

$queue = app(\Illuminate\Support\Facades\Queue::class);

?>
