<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TeamRepository")
 * @ORM\Table(name="teams")
 *
 * @codeCoverageIgnore
 */
class Team
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="strip", type="string", length=255)
     */
    private $strip;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\League", mappedBy="teams")
     */
    private $leagues;

    /**
     * Team constructor.
     */
    public function __construct()
    {
        $this->leagues = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Team
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStrip(): ?string
    {
        return $this->strip;
    }

    /**
     * @param string $strip
     *
     * @return Team
     */
    public function setStrip(string $strip): self
    {
        $this->strip = $strip;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getLeagues(): Collection
    {
        return $this->leagues;
    }

    /**
     * @param League $league
     *
     * @return $this
     */
    public function addLeague(League $league): self
    {
        $this->leagues->add($league);

        return $this;
    }

    /**
     * @param League $league
     *
     * @return $this
     */
    public function removeLeague(League $league): self
    {
        $this->leagues->removeElement($league);

        return $this;
    }
}
