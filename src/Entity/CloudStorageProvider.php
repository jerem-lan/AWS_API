<?php

namespace App\Entity;

use App\Repository\CloudStorageProviderRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CloudStorageProviderRepository::class)
 */
class CloudStorageProvider
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="json")
     */
    private $credentials = [];

    /**
     * @ORM\Column(type="string")
     */
    private $userRegion;

    /**
     * @ORM\Column(type="string")
     */
    private $provider;

    /**
     * @ORM\Column(type="string")
     */
    private $service;

    /**
     * @ORM\Column(type="json")
     */
    private $bucketsRegions;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="cloudStorageProviders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $User;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCredentials(): ?array
    {
        return $this->credentials;
    }

    public function setCredentials(array $credentials): self
    {
        $this->credentials = $credentials;

        return $this;
    }

    public function getUserRegion(): ?string
    {
        return $this->userRegion;
    }

    public function setUserRegion(string $userRegion): self
    {
        $this->userRegion = $userRegion;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;

        return $this;
    }

    public function getBucketsRegions(): ?array
    {
        return $this->bucketsRegions;
    }

    public function setBucketsRegions(array $bucketsRegions): self
    {
        $this->bucketsRegions = $bucketsRegions;

        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(string $service): self
    {
        $this->service = $service;

        return $this;
    }
}
