<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

abstract class MaybeNull
{
	public function addCover(Date $date, Money $amount = null): void
	{
		/** @var Money|null $currentAmount */
		$currentAmount = $this->covers->get($date, null);

		if ($currentAmount !== null) {
			$amount = $currentAmount->add($amount);
		}

		$this->covers->put($date, $amount);
	}

	public function addCover2(Date $date, ?Money $amount): void
	{
		/** @var Money|null $currentAmount */
		$currentAmount = $this->covers->get($date, null);

		if ($currentAmount !== null) {
			$amount = $currentAmount->add($amount);
		}

		$this->covers->put($date, $amount);
	}
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\If_\NullsafeOperatorRector\Fixture;

abstract class MaybeNull
{
	public function addCover(Date $date, Money $amount = null): void
	{
		/** @var Money|null $currentAmount */
		$currentAmount = $this->covers->get($date, null);

		$amount = $currentAmount?->add($amount);

		$this->covers->put($date, $amount);
	}

	public function addCover2(Date $date, ?Money $amount): void
	{
		/** @var Money|null $currentAmount */
		$currentAmount = $this->covers->get($date, null);

		$amount = $currentAmount?->add($amount);

		$this->covers->put($date, $amount);
	}
}

?>
