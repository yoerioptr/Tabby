<?php

declare(strict_types=1);

namespace App\Entity;

use Yoerioptr\TabtApiClient\Entries\VenueEntry;

final class CompetitionMatch
{
    private const int MATCH_DURATION_SECONDS = 10800;

    private ?string $matchId = null;

    private ?int $matchUniqueId = null;

    private ?string $weekName = null;

    private ?string $date = null;

    private ?string $time = null;

    private ?int $venue = null;

    private ?VenueEntry $venueEntry = null;

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

    public function getMatchUniqueId(): ?int
    {
        return $this->matchUniqueId;
    }

    public function setMatchUniqueId(?int $matchUniqueId): static
    {
        $this->matchUniqueId = $matchUniqueId;

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

    public function getDisplayWeekName(): ?string
    {
        if (null === $this->weekName) {
            return null;
        }

        return ctype_digit($this->weekName) ? (string) (int) $this->weekName : $this->weekName;
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

    public function getDisplayTime(): ?string
    {
        if (null === $this->time) {
            return null;
        }

        try {
            return (new \DateTimeImmutable($this->time))->format('H:i');
        } catch (\Exception) {
            return $this->time;
        }
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

    public function getVenueEntry(): ?VenueEntry
    {
        return $this->venueEntry;
    }

    public function setVenueEntry(?VenueEntry $venueEntry): static
    {
        $this->venueEntry = $venueEntry;

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

        $value = $this->date . ' ' . ($this->time ?? '00:00');

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }

    public function getEndDateTime(): ?\DateTimeImmutable
    {
        $start = $this->getDateTime();

        if (null === $start) {
            return null;
        }

        return $start->modify(sprintf('+%d seconds', self::MATCH_DURATION_SECONDS));
    }

    public function isPast(): bool
    {
        $end = $this->getEndDateTime();

        return null !== $end && $end < new \DateTimeImmutable();
    }

    public function isOngoing(): bool
    {
        $start = $this->getDateTime();
        $end = $this->getEndDateTime();
        $now = new \DateTimeImmutable();

        return null !== $start && null !== $end && $start <= $now && $now <= $end;
    }

    public function isUpcoming(): bool
    {
        $start = $this->getDateTime();

        return null !== $start && $start > new \DateTimeImmutable();
    }
}
