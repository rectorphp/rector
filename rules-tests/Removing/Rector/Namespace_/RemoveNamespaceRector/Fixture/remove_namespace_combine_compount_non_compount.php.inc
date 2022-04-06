<?php

namespace App;

use A\B\C, stdClass, D\E\F;

class RemoveNamespaceCombineCompountNonCompount
{
}

?>
-----
<?php

use A\B\C, D\E\F;
class RemoveNamespaceCombineCompountNonCompount
{
}
