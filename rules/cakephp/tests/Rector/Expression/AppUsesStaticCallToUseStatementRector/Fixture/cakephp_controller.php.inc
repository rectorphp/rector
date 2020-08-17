<?php

namespace Rector\CakePHP\Tests\Rector\Expression\AppUsesStaticCallToUseStatementRector\Fixture;

\App::uses('Component', 'Controller');

class CakeController
{
}

?>
-----
<?php

namespace Rector\CakePHP\Tests\Rector\Expression\AppUsesStaticCallToUseStatementRector\Fixture;

use Cake\Controller\Component;

class CakeController
{
}

?>
