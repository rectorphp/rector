<?php

namespace Rector\CakePHP\Tests\Rector\Namespace_\AppUsesStaticCallToUseStatementRector\Fixture;

\App::uses('HttpSocket', 'Network/Http');
\App::uses('Xml', 'Utility');
\App::uses('Component', 'Controller');
\App::uses('SomeLib', 'Data.Lib');
\App::uses('CurrencyLib', 'PluginName.Lib/Currency');
\App::uses('FooShell', 'MyPlugin.Console/Command');

// https://github.com/cakephp/upgrade/blob/05d85c147bb1302b576b818cabb66a40462aaed0/tests/test_files/AppUsesAfter.php
class CakePhpFixture
{
}

?>
-----
<?php

namespace Rector\CakePHP\Tests\Rector\Namespace_\AppUsesStaticCallToUseStatementRector\Fixture;

use App\Network\Http\HttpSocket;
use Cake\Utility\Xml;
use Cake\Controller\Component;
use Data\Lib\SomeLib;
use PluginName\Currency\CurrencyLib;
use MyPlugin\Console\Command\FooShell;

// https://github.com/cakephp/upgrade/blob/05d85c147bb1302b576b818cabb66a40462aaed0/tests/test_files/AppUsesAfter.php
class CakePhpFixture
{
}

?>
