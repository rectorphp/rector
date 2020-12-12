<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue3673\DoNotChangeAnnotationComment\Fixture;

use Doctrine\ORM\Mapping as ORM;

class SkipAnnotationComment
{
	/**
	 * @ORM\Column(name="test", type="string", length=255, nullable=true, options={"comment"="The comment fails"})
	 */
	private $test;
}

?>
