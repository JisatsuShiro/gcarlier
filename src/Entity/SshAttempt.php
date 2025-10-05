<?php

namespace App\Entity;

use App\Repository\SshAttemptRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SshAttemptRepository::class)]
#[ORM\Index(columns: ['attempted_at'], name: 'idx_attempted_at')]
#[ORM\Index(columns: ['success'], name: 'idx_success')]
class SshAttempt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 45)]
    private ?string $ipAddress = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $username = null;

    #[ORM\Column]
    private ?bool $success = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $attemptedAt = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $port = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $method = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rawLog = null;

    #[ORM\ManyToOne(inversedBy: 'sshAttempts')]
    private ?VpsServer $server = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function isSuccess(): ?bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): static
    {
        $this->success = $success;

        return $this;
    }

    public function getAttemptedAt(): ?\DateTimeImmutable
    {
        return $this->attemptedAt;
    }

    public function setAttemptedAt(\DateTimeImmutable $attemptedAt): static
    {
        $this->attemptedAt = $attemptedAt;

        return $this;
    }

    public function getPort(): ?string
    {
        return $this->port;
    }

    public function setPort(?string $port): static
    {
        $this->port = $port;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(?string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getRawLog(): ?string
    {
        return $this->rawLog;
    }

    public function setRawLog(?string $rawLog): static
    {
        $this->rawLog = $rawLog;

        return $this;
    }

    public function getServer(): ?VpsServer
    {
        return $this->server;
    }

    public function setServer(?VpsServer $server): static
    {
        $this->server = $server;

        return $this;
    }
}
