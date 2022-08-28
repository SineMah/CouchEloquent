<?php

namespace Sinemah\CouchEloquent\Eloquent;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Sinemah\CouchEloquent\Client\Document;
use Sinemah\CouchEloquent\Client\Connection;
use Sinemah\CouchEloquent\Eloquent\Traits\HasAttributes;
use Sinemah\CouchEloquent\Query\Builder as QueryBuilder;

abstract class Model
{
    use HasAttributes;

    protected Collection $collection;

    private ?string $_id = null;
    private ?string $_rev = null;

    private QueryBuilder $builder;

    protected array $original = [];

    public function __construct(protected array $attributes = [])
    {
        $this->builder = $this->newQueryBuilder();

        $this->original = $attributes;
    }

    public function setAttributes(array $attributes = []): void
    {
        $this->attributes = Arr::except($attributes, ['_id', '_rev']);
        $this->original = Arr::except($attributes, ['_id', '_rev']);
    }

    public function setId(string $id): void
    {
        $this->_id = $id;
    }

    public function setRevision(string $rev): void
    {
        $this->_rev = $rev;
    }

    public static function query(): QueryBuilder
    {
        return (new QueryBuilder(new Connection()))->setModel(new static);
    }

    protected function newQueryBuilder(): QueryBuilder
    {
        return (new QueryBuilder(new Connection()))->setModel($this);
    }

    public function getRevision(): ?string
    {
        return $this->_rev;
    }

    public function getId(): ?string
    {
        return $this->_id;
    }

    public function fromDateTime($value): ?string
    {
        return $this->asDateTime($value)->format(
            'Y-m-d H:i:s'
        );
    }

    public function save(): bool
    {
        $doc = Document::load([
            '_id' => $this->_id,
            '_rev' => $this->_rev,
        ]);

        if($doc->find()) {
            return $doc->update($this->toCastedArray());
        }

        return Document::load($this->toCastedArray())->create();
    }

    public function update(array $values = []): bool
    {
        $this->attributes = array_merge(
            $this->attributes,
            Arr::except($values, ['_id', '_rev']),
        );

        return $this->save();
    }

    public function delete(): bool
    {
        return Document::load([
            '_id' => $this->_id,
            '_rev' => $this->_rev,
        ])->delete();
    }

    public function __call($method, $parameters): mixed
    {
        return call_user_func_array([$this->newQueryBuilder(), $method], $parameters);
    }

    public static function __callStatic($method, $parameters): mixed
    {
        return (new static)->$method(...$parameters);
    }
}
