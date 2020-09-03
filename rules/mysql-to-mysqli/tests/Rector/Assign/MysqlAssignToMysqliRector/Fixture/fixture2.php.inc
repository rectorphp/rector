<?php

function mysql_to_mysqli_assign2() {
    $result = [];
    $fetch = mysql_fetch_field($result, 5);

    $fetch = mysql_fetch_field($result);

    $length = mysql_field_len($result, 5);
    $name = mysql_field_name($result, 5);
    $table = mysql_field_table($result, 5);
}

?>
-----
<?php

function mysql_to_mysqli_assign2() {
    $result = [];
    $fetch = mysqli_fetch_field_direct($result, 5);

    $fetch = mysqli_fetch_field($result);

    $length = mysqli_fetch_field_direct($result, 5)->length;
    $name = mysqli_fetch_field_direct($result, 5)->name;
    $table = mysqli_fetch_field_direct($result, 5)->table;
}

?>
