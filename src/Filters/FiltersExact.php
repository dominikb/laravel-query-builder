<?php

namespace Spatie\QueryBuilder\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FiltersExact implements Filter
{
    protected $relationConstraints = [];

    public function __invoke(Builder $query, $value, string $property) : Builder
    {
        if ($this->isRelationProperty($query, $property)) {
            return $this->withRelationConstraint($query, $value, $property);
        }

        if (is_array($value)) {
            return $query->whereIn($property, $value);
        }

        return $query->where($property, '=', $value);
    }

    protected function isRelationProperty(Builder $query, string $property) : bool
    {
        return Str::contains($property, '.')
               && ! in_array($property, $this->relationConstraints)
               && ! Str::startsWith($property, $query->getModel()->getTable());
    }

    protected function withRelationConstraint(Builder $query, $value, string $property) : Builder
    {
        [$relation, $property] = collect(explode('.', $property))
            ->pipe(function (Collection $parts) {
                return [
                    $parts->except(count($parts) - 1)->map('camel_case')->implode('.'),
                    $parts->last(),
                ];
            });

        return $query->whereHas($relation, function (Builder $query) use ($value, $relation, $property) {
            $this->relationConstraints[] = $property = $query->getModel()->getTable().'.'.$property;

            $this->__invoke($query, $value, $property);
        });
    }
}
