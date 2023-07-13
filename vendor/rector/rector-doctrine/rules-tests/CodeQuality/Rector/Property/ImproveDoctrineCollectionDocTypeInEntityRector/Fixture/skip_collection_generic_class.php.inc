<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Fixture;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Rector\Doctrine\Tests\CodeQuality\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Source\CountryRef;

#[ORM\Entity]
class SkipCollectionGenericClass
{
	/**
	 * @var Collection<int, CountryRef>
	 */
	#[ORM\OneToMany(mappedBy: 'country', targetEntity: CountryRef::class, cascade: ['persist'])]
	private Collection $countryRefs;

	public function __construct()
	{
		$this->countryRefs = new ArrayCollection();
	}
}
