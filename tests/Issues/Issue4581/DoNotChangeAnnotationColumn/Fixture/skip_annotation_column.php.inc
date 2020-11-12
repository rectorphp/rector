<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue4581\DoNotChangeAnnotationComment\Fixture;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\TimestampableEntity;

/**
 * Sessions
 *
 * @ORM\Table(name="sessions")
 * @ORM\Entity
 */
class Session
{
    /**
     * @ORM\Column(name="sess_id", type="string", length=128, nullable=false)
     * @ORM\Id
     */
    protected string $id;

    /** @ORM\Column(name="sess_data", type="blob") */
    protected string $data;

    /** @ORM\Column(name="sess_time", type="integer", options={"unsigned"=true}) */
    protected int $time;

    /** @ORM\Column(name="sess_lifetime", type="integer", options={"default"="84600"}) */
    protected int $lifetime = 84600;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     * @ORM\JoinColumn(name="user_id")
     */
    protected ?int $user = null;
}