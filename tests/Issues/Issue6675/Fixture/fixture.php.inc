<?php

namespace Rector\Core\Tests\Issues\Issue6675\Fixture;

$db = mysql_connect("server","user","password");
echo mysql_error();

?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue6675\Fixture;

$db = mysqli_connect("server","user","password");
echo mysql_error();

?>
