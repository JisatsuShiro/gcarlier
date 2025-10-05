<?php

namespace App\Entity;

use App\Repository\VpsMetricRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VpsMetricRepository::class)]
class VpsMetric
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $cpuUsage = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $memoryUsage = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)]
    private ?string $diskUsage = null;

    #[ORM\Column(type: 'bigint', nullable: true)]
    private ?string $networkIn = null;

    #[ORM\Column(type: 'bigint', nullable: true)]
    private ?string $networkOut = null;

    #[ORM\Column(nullable: true)]
    private ?int $uptime = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $recordedAt = null;

    #[ORM\ManyToOne(inversedBy: 'metrics')]
    #[ORM\JoinColumn(nullable: false)]
    private ?VpsServer $server = null;

    public function __construct()
    {
        $this->recordedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCpuUsage(): ?string
    {
        return $this->cpuUsage;
    }

    public function setCpuUsage(?string $cpuUsage): static
    {
        $this->cpuUsage = $cpuUsage;

        return $this;
    }

    public function getMemoryUsage(): ?string
    {
        return $this->memoryUsage;
    }

    public function setMemoryUsage(?string $memoryUsage): static
    {
        $this->memoryUsage = $memoryUsage;

        return $this;
    }

    public function getDiskUsage(): ?string
    {
        return $this->diskUsage;
    }

    public function setDiskUsage(?string $diskUsage): static
    {
        $this->diskUsage = $diskUsage;

        return $this;
    }

    public function getNetworkIn(): ?string
    {
        return $this->networkIn;
    }

    public function setNetworkIn(?string $networkIn): static
    {
        $this->networkIn = $networkIn;

        return $this;
    }

    public function getNetworkOut(): ?string
    {
        return $this->networkOut;
    }

    public function setNetworkOut(?string $networkOut): static
    {
        $this->networkOut = $networkOut;

        return $this;
    }

    public function getUptime(): ?int
    {
        return $this->uptime;
    }

    public function setUptime(?int $uptime): static
    {
        $this->uptime = $uptime;

        return $this;
    }

    public function getRecordedAt(): ?\DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function setRecordedAt(\DateTimeImmutable $recordedAt): static
    {
        $this->recordedAt = $recordedAt;

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
