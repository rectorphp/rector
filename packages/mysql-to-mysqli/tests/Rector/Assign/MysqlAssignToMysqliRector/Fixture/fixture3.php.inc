<?php

function mysql_to_mysqli_assign3() {
    $result = [];
    $name = mysql_tablename($result, 3);
}

?>
-----
<?php

function mysql_to_mysqli_assign3() {
    $result = [];
    mysqli_data_seek($result, 3);
    $name = mysql_fetch_array($result)[0];
}

?>
