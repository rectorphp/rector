<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector\Fixture;

trait MessageTrait
{
    /**
     * @return $this
     */
	public function withStatus(): self
	{
		return $this;
	}
}

class Response
{
	use MessageTrait;

	/**
	 * @return $this
	 */
	public function show()
	{
		$response = $this;
		return $response->withStatus();
	}
}

class ConsumerResponse
{
	/**
	 * @var Response
	 */
	public $response;

	public function get(): Response
	{
		return $this->response->show();
	}
}