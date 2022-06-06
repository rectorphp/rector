<?php

namespace Company\Commands\Queue;

use CompanyB\Queue;

$queue = App::run(\Queue::class);

?>
-----
<?php

namespace Company\Commands\Queue;

use CompanyB\Queue;

$queue = App::run(\Illuminate\Support\Facades\Queue::class);

?>
