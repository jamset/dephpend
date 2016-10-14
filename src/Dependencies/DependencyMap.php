<?php

declare(strict_types=1);

namespace Mihaeu\PhpDependencies\Dependencies;

use Mihaeu\PhpDependencies\Util\AbstractMap;

class DependencyMap extends AbstractMap
{
    /**
     * @param Dependency $from
     * @param Dependency $to
     *
     * @return DependencyMap
     */
    public function add(Dependency $from, Dependency $to) : self
    {
        $clone = clone $this;
        if ($from->equals($to)) {
            return $clone;
        }

        if (array_key_exists($from->toString(), $clone->map)) {
            $clone->map[$from->toString()][self::$VALUE] = $clone->map[$from->toString()][self::$VALUE]->add($to);
        } else {
            $clone->map[$from->toString()] = [
                self::$KEY      => $from,
                self::$VALUE    => (new DependencySet())->add($to),
            ];
        }
        return $clone;
    }

    public function addSet(Dependency $from, DependencySet $toSet) : self
    {
        $clone = $toSet->reduce($this, function (DependencyMap $map, Dependency $to) use ($from) {
            return $map->add($from, $to);
        });
        return $clone;
    }

    public function get(Dependency $from) : DependencySet
    {
        return $this->map[$from->toString()][self::$VALUE];
    }

    /**
     * @return DependencySet
     */
    public function fromDependencies() : DependencySet
    {
        return $this->reduce(new DependencySet(), function (DependencySet $set, Dependency $from, Dependency $to) {
            return $set->add($from);
        });
    }

    /**
     * @return DependencySet
     */
    public function allDependencies() : DependencySet
    {
        return $this->reduce(new DependencySet(), function (DependencySet $set, Dependency $from, Dependency $to) {
            return $set
                ->add($from)
                ->add($to);
        });
    }

    public function mapAllDependencies(\Closure $mappers) : DependencySet
    {
        return $this->reduce(new DependencySet(), function (DependencySet $set, Dependency $from, Dependency $to) use ($mappers) {
            return $set
                ->add($mappers($from))
                ->add($mappers($to));
        });
    }

    /**
     * @inheritDoc
     */
    public function toString() : string
    {
        return trim($this->reduce('', function (string $carry, Dependency $key, Dependency $value) {
            return $value instanceof NullDependency
                ? $carry
                : $carry.$key->toString().' --> '.$value->toString().PHP_EOL;
        }));
    }
}
