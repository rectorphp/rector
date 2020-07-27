<?php

function mysql_to_mysqli_assign() {
    $result = [];
    $row = 1;
    $data = mysql_db_name($result, $row);

    $result = mysql_db_query('database', 'query');
}

?>
-----
<?php

function mysql_to_mysqli_assign() {
    $result = [];
    $row = 1;
    mysqli_data_seek($result, $row);
    $fetch = mysqli_fetch_row($result);
    $data = $fetch[0];

    mysqli_select_db('database');
    $result = mysqli_query('query');
}

?>
