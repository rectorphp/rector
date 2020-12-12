<?php

namespace Rector\CakePHP\Tests\Rector\Namespace_\AppUsesStaticCallToUseStatementRector\Fixture;

\App::uses('Component', 'Controller');

class CakephpController
{
}

?>
-----
<?php

namespace Rector\CakePHP\Tests\Rector\Namespace_\AppUsesStaticCallToUseStatementRector\Fixture;

use Cake\Controller\Component;

class CakephpController
{
}

?>
