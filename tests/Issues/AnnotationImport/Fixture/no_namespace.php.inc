<?php

/**
 * @Doctrine\ORM\Mapping\Table("Table_Name")
 */
class NoNamespace
{
}

?>
-----
<?php

use Doctrine\ORM\Mapping\Table;
/**
 * @Table("Table_Name")
 */
class NoNamespace
{
}
