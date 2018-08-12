<?php

if (\Environment::getEnv('SS_DATABASE_NAME')) {
    echo SS_DATABASENAME;
}
