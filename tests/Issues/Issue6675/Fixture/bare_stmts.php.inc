<?php

namespace Rector\Core\Tests\Issues\Issue6675\Fixture;

function someFunction()
{
    $db = mysql_connect("server", "user", "password");
    echo mysql_error();
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue6675\Fixture;

function someFunction()
{
    $db = mysqli_connect("server", "user", "password");
    echo mysqli_error($db);
}

?>
