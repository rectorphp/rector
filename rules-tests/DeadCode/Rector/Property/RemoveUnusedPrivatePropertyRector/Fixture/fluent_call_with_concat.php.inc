<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class FluentCallWithConcat
{
	private static $directory = 'CacheFactory';
	private $cacheFactory;
	private $config;

    public function run()
	{
		$this->config             = new Cache();
		$this->config->storePath .= self::$directory;
	}
}

?>
-----
<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

class FluentCallWithConcat
{
	private static $directory = 'CacheFactory';
	private $config;

    public function run()
	{
		$this->config             = new Cache();
		$this->config->storePath .= self::$directory;
	}
}

?>
