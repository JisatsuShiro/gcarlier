<?php

namespace App\Entity;

use App\Repository\VpsServerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VpsServerRepository::class)]
class VpsServer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 45)]
    private ?string $ipAddress = null;

    #[ORM\Column]
    private ?int $sshPort = 22;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $sshUser = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $provider = null;

    #[ORM\Column(length: 20)]
    private ?string $status = 'active';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'vpsServers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, VpsMetric>
     */
    #[ORM\OneToMany(targetEntity: VpsMetric::class, mappedBy: 'server', orphanRemoval: true)]
    private Collection $metrics;

    /**
     * @var Collection<int, SshAttempt>
     */
    #[ORM\OneToMany(targetEntity: SshAttempt::class, mappedBy: 'server', orphanRemoval: true)]
    private Collection $sshAttempts;

    public function __construct()
    {
        $this->metrics = new ArrayCollection();
        $this->sshAttempts = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
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

    public function getSshPort(): ?int
    {
        return $this->sshPort;
    }

    public function setSshPort(int $sshPort): static
    {
        $this->sshPort = $sshPort;

        return $this;
    }

    public function getSshUser(): ?string
    {
        return $this->sshUser;
    }

    public function setSshUser(?string $sshUser): static
    {
        $this->sshUser = $sshUser;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(?string $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, VpsMetric>
     */
    public function getMetrics(): Collection
    {
        return $this->metrics;
    }

    public function addMetric(VpsMetric $metric): static
    {
        if (!$this->metrics->contains($metric)) {
            $this->metrics->add($metric);
            $metric->setServer($this);
        }

        return $this;
    }

    public function removeMetric(VpsMetric $metric): static
    {
        if ($this->metrics->removeElement($metric)) {
            // set the owning side to null (unless already changed)
            if ($metric->getServer() === $this) {
                $metric->setServer(null);
            }
        }

        return $this;
    }

    public function getLatestMetric(): ?VpsMetric
    {
        $metrics = $this->metrics->toArray();
        if (empty($metrics)) {
            return null;
        }

        usort($metrics, function($a, $b) {
            return $b->getRecordedAt() <=> $a->getRecordedAt();
        });

        return $metrics[0];
    }

    /**
     * @return Collection<int, SshAttempt>
     */
    public function getSshAttempts(): Collection
    {
        return $this->sshAttempts;
    }

    public function addSshAttempt(SshAttempt $sshAttempt): static
    {
        if (!$this->sshAttempts->contains($sshAttempt)) {
            $this->sshAttempts->add($sshAttempt);
            $sshAttempt->setServer($this);
        }

        return $this;
    }

    public function removeSshAttempt(SshAttempt $sshAttempt): static
    {
        if ($this->sshAttempts->removeElement($sshAttempt)) {
            if ($sshAttempt->getServer() === $this) {
                $sshAttempt->setServer(null);
            }
        }

        return $this;
    }
}
