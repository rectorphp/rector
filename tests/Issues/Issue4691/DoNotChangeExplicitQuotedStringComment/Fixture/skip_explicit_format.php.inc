<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue4691\DoNotChangeExplicitQuotedStringComment\Fixture;

final class DemoFile
{
	/**
	 * @var boolean|"frozen"|array
	 */
	public $use_session = \true;
}
