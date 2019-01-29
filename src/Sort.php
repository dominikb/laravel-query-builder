<?php

namespace Spatie\QueryBuilder;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Sorts\SortsField;
use Spatie\QueryBuilder\Sorts\Sort as CustomSort;

class Sort
{
    /** @var string */
    protected $sortClass;

    /** @var string */
    protected $property;

    /** @var string  */
    protected $columnName;

    public function __construct(string $property, $sortClass, ? string $columnName)
    {
        $this->property = $property;
        $this->sortClass = $sortClass;
        $this->columnName = $columnName ?? $property;
    }

    public function sort(Builder $builder, $descending)
    {
        $sortClass = $this->resolveSortClass();

        ($sortClass)($builder, $descending, $this->columnName);
    }

    public static function field(string $property, ? string $columnName = null) : self
    {
        return new static($property, SortsField::class, $columnName);
    }

    public static function custom(string $property, $sortClass, ? string $columnName = null) : self
    {
        return new static($property, $sortClass, $columnName);
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getColumnName(): string
    {
        return $this->columnName;
    }

    public function isForProperty(string $property): bool
    {
        return $this->property === $property;
    }

    private function resolveSortClass(): CustomSort
    {
        if ($this->sortClass instanceof CustomSort) {
            return $this->sortClass;
        }

        return new $this->sortClass;
    }
}
