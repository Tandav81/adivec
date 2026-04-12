<?php

namespace App\Entity;

use App\Repository\PageViewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageViewRepository::class)]
#[ORM\Index(name: 'visited_at_idx', columns: ['visited_at'])]
#[ORM\Index(name: 'url_idx', columns: ['url'])]
class PageView
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 191)]
    private string $url = '';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $visitedAt;

    /** IP hashée (SHA-256) — jamais stockée en clair pour la conformité RGPD */
    #[ORM\Column(length: 64, nullable: true)]
    private ?string $ipHash = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(length: 191, nullable: true)]
    private ?string $referer = null;

    public function __construct()
    {
        $this->visitedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getUrl(): string { return $this->url; }
    public function setUrl(string $url): static { $this->url = $url; return $this; }

    public function getVisitedAt(): \DateTimeImmutable { return $this->visitedAt; }

    public function getIpHash(): ?string { return $this->ipHash; }
    public function setIpHash(?string $ipHash): static { $this->ipHash = $ipHash; return $this; }

    public function getUserAgent(): ?string { return $this->userAgent; }
    public function setUserAgent(?string $userAgent): static { $this->userAgent = $userAgent; return $this; }

    public function getReferer(): ?string { return $this->referer; }
    public function setReferer(?string $referer): static { $this->referer = $referer; return $this; }
}
