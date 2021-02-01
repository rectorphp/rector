<?php

interface MyFooInterface {};
interface MyBarInterface {};

final class DemoFile implements \MyFooInterface, \MyBarInterface
{

}

?>
-----
<?php

interface MyBazInterface {};

final class DemoFile implements \MyBazInterface
{

}

?>
