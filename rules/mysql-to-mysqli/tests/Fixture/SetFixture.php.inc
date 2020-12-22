<?php

declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests\Fixture;

final class Setfixture
{
    public function run()
    {
        $sql = 'SELECT';

        $connection = mysql_connect('host', 'user', 'pass', true, MYSQL_CLIENT_IGNORE_SPACE);
        $connection2 = mysql_pconnect('host2', 'user2', 'pass2', MYSQL_CLIENT_COMPRESS | MYSQL_CLIENT_INTERACTIVE);

        mysql_get_client_info();

        mysql_set_charset('utf8');
        mysql_select_db('db_name');

        mysql_error();
        mysql_get_host_info();
        mysql_get_proto_info();
        mysql_get_server_info();
        mysql_info();
        mysql_insert_id();
        mysql_ping();
        mysql_stat();
        mysql_thread_id();

        $result = mysql_query($sql);
        $result2 = mysql_query($sql);

        mysql_errno();

        mysql_num_fields($result);
        mysql_num_rows($result);
        mysql_field_seek($result, 3);
        mysql_data_seek($result, 42);
        mysql_fetch_array($result, MYSQL_ASSOC);
        mysql_fetch_assoc($result);
        mysql_fetch_lengths($result);
        mysql_fetch_object($result);
        mysql_fetch_row($result);

        mysql_free_result($result);

        mysql_escape_string("Howdy -- Mr. O'Neil");
        mysql_real_escape_string("Howdy -- Mr. O'Neil");

        echo mysql_affected_rows();
        mysql_client_encoding();

        return mysql_close();
    }

    public function run_with_connection()
    {
        $sql = 'SELECT';

        $connection = mysql_connect('host', 'user', 'pass', true, MYSQL_CLIENT_IGNORE_SPACE);
        $connection2 = mysql_pconnect('host2', 'user2', 'pass2', MYSQL_CLIENT_COMPRESS | MYSQL_CLIENT_INTERACTIVE);

        mysql_get_client_info();

        mysql_set_charset('utf8', $connection);
        mysql_select_db('db_name', $connection);

        mysql_error($connection);
        mysql_get_host_info($connection);
        mysql_get_proto_info($connection);
        mysql_get_server_info($connection);
        mysql_info($connection);
        mysql_insert_id($connection);
        mysql_ping($connection);
        mysql_stat($connection);
        mysql_thread_id($connection);

        $result = mysql_query($sql, $connection);
        $result2 = mysql_query($sql, $connection);

        mysql_errno($connection);

        mysql_num_fields($result);
        mysql_num_rows($result);
        mysql_field_seek($result, 3);
        mysql_data_seek($result, 42);
        mysql_fetch_array($result, MYSQL_ASSOC);
        mysql_fetch_assoc($result);
        mysql_fetch_lengths($result);
        mysql_fetch_object($result);
        mysql_fetch_row($result);

        mysql_free_result($result);

        mysql_escape_string("Howdy -- Mr. O'Neil");
        mysql_real_escape_string("Howdy -- Mr. O'Neil", $connection);

        echo mysql_affected_rows($connection);
        mysql_client_encoding($connection);

        return mysql_close($connection);
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\MysqlToMysqli\Tests\Fixture;

final class Setfixture
{
    public function run()
    {
        $sql = 'SELECT';

        $connection = mysqli_connect('host', 'user', 'pass');
        $connection2 = mysqli_connect('p:host2', 'user2', 'pass2');

        mysqli_get_client_info();

        mysqli_set_charset($connection2, 'utf8');
        mysqli_select_db($connection2, 'db_name');

        mysqli_error($connection2);
        mysqli_get_host_info($connection2);
        mysqli_get_proto_info($connection2);
        mysqli_get_server_info($connection2);
        mysqli_info($connection2);
        mysqli_insert_id($connection2);
        mysqli_ping($connection2);
        mysqli_stat($connection2);
        mysqli_thread_id($connection2);

        $result = mysqli_query($connection2, $sql);
        $result2 = mysqli_query($connection2, $sql);

        mysqli_errno($connection2);

        mysqli_num_fields($result);
        mysqli_num_rows($result);
        mysqli_field_seek($result, 3);
        mysqli_data_seek($result, 42);
        mysqli_fetch_array($result, MYSQLI_ASSOC);
        mysqli_fetch_assoc($result);
        mysqli_fetch_lengths($result);
        mysqli_fetch_object($result);
        mysqli_fetch_row($result);

        mysqli_free_result($result);

        mysqli_real_escape_string($connection2, "Howdy -- Mr. O'Neil");
        mysqli_real_escape_string($connection2, "Howdy -- Mr. O'Neil");

        echo mysqli_affected_rows($connection2);
        mysqli_character_set_name($connection2);

        return mysqli_close($connection2);
    }

    public function run_with_connection()
    {
        $sql = 'SELECT';

        $connection = mysqli_connect('host', 'user', 'pass');
        $connection2 = mysqli_connect('p:host2', 'user2', 'pass2');

        mysqli_get_client_info();

        mysqli_set_charset($connection, 'utf8');
        mysqli_select_db($connection, 'db_name');

        mysqli_error($connection);
        mysqli_get_host_info($connection);
        mysqli_get_proto_info($connection);
        mysqli_get_server_info($connection);
        mysqli_info($connection);
        mysqli_insert_id($connection);
        mysqli_ping($connection);
        mysqli_stat($connection);
        mysqli_thread_id($connection);

        $result = mysqli_query($connection, $sql);
        $result2 = mysqli_query($connection, $sql);

        mysqli_errno($connection);

        mysqli_num_fields($result);
        mysqli_num_rows($result);
        mysqli_field_seek($result, 3);
        mysqli_data_seek($result, 42);
        mysqli_fetch_array($result, MYSQLI_ASSOC);
        mysqli_fetch_assoc($result);
        mysqli_fetch_lengths($result);
        mysqli_fetch_object($result);
        mysqli_fetch_row($result);

        mysqli_free_result($result);

        mysqli_real_escape_string($connection2, "Howdy -- Mr. O'Neil");
        mysqli_real_escape_string($connection, "Howdy -- Mr. O'Neil");

        echo mysqli_affected_rows($connection);
        mysqli_character_set_name($connection);

        return mysqli_close($connection);
    }
}

?>
