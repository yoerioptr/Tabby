<?php

declare(strict_types=1);

namespace App\Entity;

final class Member
{
    private ?int $id = null;

    private ?string $firstName = null;

    private ?string $lastName = null;

    private ?string $ranking = null;

    private ?string $clubId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getRanking(): ?string
    {
        return $this->ranking;
    }

    public function setRanking(?string $ranking): static
    {
        $this->ranking = $ranking;

        return $this;
    }

    public function getClubId(): ?string
    {
        return $this->clubId;
    }

    public function setClubId(?string $clubId): static
    {
        $this->clubId = $clubId;

        return $this;
    }
}
