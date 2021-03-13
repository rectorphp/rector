<?php

function mysql_to_mysqli_func_call() {
    mysql_create_db('database_name');

    $database = 'database_name';
    mysql_create_db($database);

    mysql_drop_db('database_name');

    $database = 'database_name';
    mysql_drop_db($database);

    $list = mysql_list_dbs();
    $list = mysql_list_tables($database);

    mysql_list_fields($database_name, $table_name);
}

?>
-----
<?php

function mysql_to_mysqli_func_call() {
    mysqli_query('CREATE DATABASE database_name');

    $database = 'database_name';
    mysqli_query('CREATE DATABASE ' . $database);

    mysqli_query('DROP DATABASE database_name');

    $database = 'database_name';
    mysqli_query('DROP DATABASE ' . $database);

    $list = mysqli_query('SHOW DATABASES');
    $list = mysqli_query('SHOW TABLES FROM ' . $database);

    mysqli_query('SHOW COLUMNS FROM ' . $table_name);
}

?>
