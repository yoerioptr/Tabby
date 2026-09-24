<?php

declare(strict_types=1);

namespace App\Entity;

final class CompetitionMatch
{
    private ?string $matchId = null;

    private ?string $weekName = null;

    private ?string $date = null;

    private ?string $time = null;

    private ?int $venue = null;

    private ?string $homeClub = null;

    private ?string $homeTeam = null;

    private ?string $awayClub = null;

    private ?string $awayTeam = null;

    private ?string $score = null;

    private ?int $divisionId = null;

    private ?string $divisionName = null;

    private bool $isValidated = false;

    public function getMatchId(): ?string
    {
        return $this->matchId;
    }

    public function setMatchId(?string $matchId): static
    {
        $this->matchId = $matchId;

        return $this;
    }

    public function getWeekName(): ?string
    {
        return $this->weekName;
    }

    public function setWeekName(?string $weekName): static
    {
        $this->weekName = $weekName;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getTime(): ?string
    {
        return $this->time;
    }

    public function setTime(?string $time): static
    {
        $this->time = $time;

        return $this;
    }

    public function getVenue(): ?int
    {
        return $this->venue;
    }

    public function setVenue(?int $venue): static
    {
        $this->venue = $venue;

        return $this;
    }

    public function getHomeClub(): ?string
    {
        return $this->homeClub;
    }

    public function setHomeClub(?string $homeClub): static
    {
        $this->homeClub = $homeClub;

        return $this;
    }

    public function getHomeTeam(): ?string
    {
        return $this->homeTeam;
    }

    public function setHomeTeam(?string $homeTeam): static
    {
        $this->homeTeam = $homeTeam;

        return $this;
    }

    public function getAwayClub(): ?string
    {
        return $this->awayClub;
    }

    public function setAwayClub(?string $awayClub): static
    {
        $this->awayClub = $awayClub;

        return $this;
    }

    public function getAwayTeam(): ?string
    {
        return $this->awayTeam;
    }

    public function setAwayTeam(?string $awayTeam): static
    {
        $this->awayTeam = $awayTeam;

        return $this;
    }

    public function getScore(): ?string
    {
        return $this->score;
    }

    public function setScore(?string $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getDivisionId(): ?int
    {
        return $this->divisionId;
    }

    public function setDivisionId(?int $divisionId): static
    {
        $this->divisionId = $divisionId;

        return $this;
    }

    public function getDivisionName(): ?string
    {
        return $this->divisionName;
    }

    public function setDivisionName(?string $divisionName): static
    {
        $this->divisionName = $divisionName;

        return $this;
    }

    public function isValidated(): bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(bool $isValidated): static
    {
        $this->isValidated = $isValidated;

        return $this;
    }

    public function getDateTime(): ?\DateTimeImmutable
    {
        if (null === $this->date) {
            return null;
        }

        $value = $this->date.' '.($this->time ?? '00:00');

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }
}
