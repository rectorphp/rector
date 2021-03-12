<?php

function mysql_to_mysqli_assign4() {
    $result = [];
    $row = 1;
    $data = mysql_result($result, $row);

    $result = [];
    $row = 1;
    $field = 1;
    $data = mysql_result($result, $row, $field);
}

?>
-----
<?php

function mysql_to_mysqli_assign4() {
    $result = [];
    $row = 1;
    mysqli_data_seek($result, $row);
    $fetch = mysqli_fetch_array($result);
    $data = $fetch[0];

    $result = [];
    $row = 1;
    $field = 1;
    mysqli_data_seek($result, $row);
    $fetch = mysqli_fetch_array($result);
    $data = $fetch[$field];
}

?>
