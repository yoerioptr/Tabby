<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CompetitionMatch;
use Doctrine\Common\Collections\ArrayCollection;
use Yoerioptr\TabtApiBundle\Doctrine\ApiFetcher;
use Yoerioptr\TabtApiBundle\Doctrine\EntityHydrator;
use Yoerioptr\TabtApiBundle\Doctrine\MappingRegistry;
use Yoerioptr\TabtApiBundle\Repository\AbstractTabtRepository;

/**
 * @extends AbstractTabtRepository<CompetitionMatch>
 */
final class MatchRepository extends AbstractTabtRepository
{
    private ?ArrayCollection $matchCollection = null;

    public function __construct(
        private readonly MappingRegistry $mappings,
        private readonly ApiFetcher $fetcher,
        EntityHydrator $hydrator,
    ) {
        parent::__construct($mappings, $hydrator, $fetcher);
    }

    /**
     * Match entries only include a subset of their (non-nullable) fields
     * depending on the match state (e.g. an unplayed match has no venue or
     * unique id). The bundle hydrator calls every mapped getter directly,
     * which throws on uninitialized typed properties, so hydrate defensively.
     *
     * @return ArrayCollection<int, CompetitionMatch>
     */
    protected function getCollection(): ArrayCollection
    {
        if (null !== $this->matchCollection) {
            return $this->matchCollection;
        }

        $mapping = $this->mappings->forEntity($this->getEntityClass());
        $collection = new ArrayCollection();

        foreach ($this->fetcher->fetch($mapping->getSource(), $this->getFetchParameters()) as $entry) {
            $collection->add($this->hydrate($entry, $mapping->getFields()));
        }

        return $this->matchCollection = $collection;
    }

    protected function getEntityClass(): string
    {
        return CompetitionMatch::class;
    }

    /**
     * @param array<string, string> $fields entry field => entity property
     */
    private function hydrate(object $entry, array $fields): CompetitionMatch
    {
        $match = new CompetitionMatch();

        foreach ($fields as $entryField => $property) {
            $setter = 'set'.ucfirst((string) $property);

            if (method_exists($match, $setter)) {
                $match->$setter($this->read($entry, (string) $entryField));
            }
        }

        return $match;
    }

    private function read(object $entry, string $field): mixed
    {
        $getter = 'get'.ucfirst($field);

        if (method_exists($entry, $getter)) {
            try {
                return $entry->$getter();
            } catch (\Error) {
                // Typed property was never initialized by the API response.
                return null;
            }
        }

        $property = lcfirst($field);

        if (!property_exists($entry, $property)) {
            return null;
        }

        try {
            $reflection = new \ReflectionProperty($entry, $property);
        } catch (\ReflectionException) {
            return null;
        }

        return $reflection->isInitialized($entry) ? $reflection->getValue($entry) : null;
    }
}
