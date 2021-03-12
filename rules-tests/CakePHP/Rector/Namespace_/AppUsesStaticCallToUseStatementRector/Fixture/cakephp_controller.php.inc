<?php

namespace Rector\Tests\CakePHP\Rector\Namespace_\AppUsesStaticCallToUseStatementRector\Fixture;

\App::uses('Component', 'Controller');

class CakeController
{
}

?>
-----
<?php

namespace Rector\Tests\CakePHP\Rector\Namespace_\AppUsesStaticCallToUseStatementRector\Fixture;

use Cake\Controller\Component;

class CakeController
{
}

?>
