<?php

function constantToStaticCall()
{
    if (defined('SS_DATABASE_NAME')) {
        echo SS_DATABASENAME;
    }
}

?>
-----
<?php

function constantToStaticCall()
{
    if (defined('SS_DATABASE_NAME')) {
        echo \Environment::getEnv('SS_DATABASENAME');
    }
}

?>
