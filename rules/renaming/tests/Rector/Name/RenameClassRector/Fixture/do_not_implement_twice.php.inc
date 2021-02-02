<?php

interface MyFooInterface {};
interface MyBarInterface {};

final class DoNotImplementTwice implements \MyFooInterface, \MyBarInterface
{
}

?>
-----
<?php

interface MyFooInterface {};
interface MyBarInterface {};

final class DoNotImplementTwice implements \MyBazInterface
{
}

?>
