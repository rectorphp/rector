<?php

$db = mysql_connect("server", "user", "password");
echo mysql_error();

?>
-----
<?php

$db = mysqli_connect("server", "user", "password");
echo mysqli_error($db);

?>
