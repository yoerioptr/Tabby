<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Yoerioptr\TabtApiBundle\ReadModel\ReadModel;

/**
 * @template T of object
 */
abstract class TabtRepository
{
    public function __construct(private readonly ReadModel $readModel)
    {
        //
    }

    /**
     * @return class-string<T>
     */
    abstract protected function modelClass(): string;

    protected function query(): QueryBuilder
    {
        return $this->readModel->query($this->modelClass())->select('*');
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return list<T>
     */
    protected function hydrate(array $rows): array
    {
        return $this->readModel->hydrate($this->modelClass(), $rows);
    }
}
