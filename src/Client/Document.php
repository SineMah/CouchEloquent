<?php

namespace Sinemah\CouchEloquent\Client;

use Illuminate\Support\Arr;

class Document
{
    private ?string $id;
    private ?string $rev;

    private Builder $builder;

    public static function load($values): Document
    {
        return new self($values);
    }

    private function __construct(private $values)
    {
        $this->builder = Builder::load(new Connection());

        $this->id = $values['_id'] ?? null;
        $this->rev = $values['_rev'] ?? null;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function rev(): string
    {
        return $this->rev;
    }

    public function find(): bool
    {
        $doc = $this->builder->find($this->values['_id'] ?? null) ?? [];

        $this->values = array_merge(
            $this->values,
            $doc
        );

        return count($doc) > 0;
    }

    public function create(): bool
    {
        if($values = $this->builder->create($this->toArray())) {
            $this->values = $values;
            $this->id = $values['_id'];
            $this->rev = $values['_rev'];

            return true;
        }

        return false;
    }

    public function update(array $payload = []): bool
    {
        $this->values = $payload;

        if($values = $this->builder->update($this->id(), $this->toArray())) {
            $this->values = $values;
            $this->rev = $values['_rev'];

            return true;
        }

        return false;
    }

    public function delete(): bool
    {
        return $this->builder->delete($this->id);
    }

    public function toArray(): array
    {
        return Arr::except($this->values, ['_id', '_rev']);
    }
}