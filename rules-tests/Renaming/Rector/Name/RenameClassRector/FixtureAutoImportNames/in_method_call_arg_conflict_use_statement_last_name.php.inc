<?php

namespace Company\Commands\Queue;

use CompanyB\Queue;

$app = new App();
$queue = $app->run(\Queue::class);

?>
-----
<?php

namespace Company\Commands\Queue;

use CompanyB\Queue;

$app = new App();
$queue = $app->run(\Illuminate\Support\Facades\Queue::class);

?>
