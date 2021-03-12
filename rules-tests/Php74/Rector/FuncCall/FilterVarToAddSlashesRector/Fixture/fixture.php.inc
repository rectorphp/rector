<?php

namespace Rector\Tests\Php74\Rector\FuncCall\FilterVarToAddSlashesRector\Fixture;

$var= "Satya's here!";
filter_var($var, FILTER_SANITIZE_MAGIC_QUOTES);

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\FuncCall\FilterVarToAddSlashesRector\Fixture;

$var= "Satya's here!";
addslashes($var);

?>
