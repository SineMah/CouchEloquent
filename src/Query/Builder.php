<?php

namespace Sinemah\CouchEloquent\Query;

use Closure;
use Sinemah\CouchEloquent\Client\Builder as ClientBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Sinemah\CouchEloquent\Client\Connection;
use Sinemah\CouchEloquent\Client\Document;
use Sinemah\CouchEloquent\Eloquent\Model;
use Sinemah\CouchEloquent\Query\Parser\QueryFactory;
use Sinemah\CouchEloquent\Query\Parser\WhereFactory;

class Builder
{
    public array $bindings = [
        'select' => [],
        'from' => [],
        'join' => [],
        'where' => [],
        'groupBy' => [],
        'order' => [],
    ];

    public array $aggregate;

    public array $columns;

    public string $from;

    public array $wheres = [];

    public array $orders;

    public int $limit;

    public int $offset;

    public int $timeout;

    public int $hint;

    public array $options = [];

//    public array $operators = [
//        '=',
//        '<',
//        '>',
//        '<=',
//        '>=',
//        '<>',
//        '!=',
//        'like',
//        'not like',
//        'between',
//        'ilike',
//        'not ilike',
//        'all',
//        '&',
//        '|',
//        'exists',
//        'type',
//        'mod',
//        'where',
//        'size',
//        'regex',
//        'not regex',
//        'elemmatch',
//    ];

    protected array $operators = [
        '!' => '$ne',
        '!=' => '$ne',
        'eq'  => '$eq',
        '<>' => '$ne',
        '<'  => '$lt',
        '<=' => '$lte',
        '>'  => '$gt',
        '>=' => '$gte',
    ];

    protected bool $useCollections;
    protected ClientBuilder $builder;

    protected Model $model;

    public function __construct(protected Connection $connection)
    {
        $this->builder = ClientBuilder::load($connection);
        $this->useCollections = true;
    }

    public function insert(array $values): bool
    {
        $saved = 0;

        if(Arr::isAssoc($values)) {
            $values = [$values];
        }

        collect($values)->each(function(array $payload) use (&$saved) {
            $model = $this->getLoadedModel($payload, true);
            $doc = Document::load($model->toArray());

            $saved += (int) $doc->create();
        });

        return $saved === count($values);
    }

    public function count($columns = ['*']): int
    {
        return $this->get()->count();
    }

    public function update(array $values, array $options = []): bool
    {
        $updated = 0;
        $docs = $this->get();

        collect($docs)->each(function(array $doc) use (&$updated, $values) {
            $model =$this->getLoadedModel($doc, true);
            $doc = Document::load($model->toArray());

            $updated += (int) $doc->update($values);
        });

        return $updated === count($docs);
    }

    public function find(string $id, $columns = ['*'])
    {
        return $this->where('_id', $id)->first();
    }

    public function where(string|Closure $column, mixed $operator = null, mixed $value = null, string $boolean = 'and', bool $not = false): Builder
    {
        $type = 'basic';

        if(gettype($column) === 'object' && get_class($column) === 'Closure') {
            $value = $column(new Builder($this->connection));
            $column = null;
            $type = 'nested';
        }

        $this->wheres[] = [
            'type'    => $type,
            'operator'=> $operator,
            'column'  => $column,
            'value'   => $value,
            'boolean' => $boolean,
            'not' => $not,
        ];

        return $this;
    }

    public function orWhere(string|callable $column, mixed $operator = null, mixed $value = null, bool $not = false): Builder
    {
        return $this->where($column, $operator, $value, 'or', $not);
    }

    public function whereNot(string $column, mixed $operator = null, mixed $value = null, string $boolean = 'and'): Builder
    {
        return $this->where($column, $operator, $value, $boolean, true);
    }

    public function orWhereNot(string $column, mixed $operator = null, mixed $value = null): Builder
    {
        return $this->whereNot($column, $operator, $value, 'or');
    }

    public function whereIn(string $column, iterable $values, string $boolean = 'and', bool $not = false): Builder
    {
        $this->wheres[] = [
            'type' => 'type_in',
            'operator' => null,
            'column'  => $column,
            'value'   => $values,
            'boolean' => $boolean,
            'not' => $not,
        ];

        return $this;
    }

    public function orWhereIn(string $column, iterable $values): Builder
    {
        return $this->whereIn($column, $values, 'or');
    }

    public function whereNotIn($column, $values, $boolean = 'and'): Builder
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    public function orWhereNotIn(string $column, iterable $values): Builder
    {
        return $this->whereNotIn($column, $values, 'or');
    }

    public function whereNull(string $column, string $boolean = 'and', bool $not = false): Builder
    {
        $this->wheres[] = [
            'type'    => 'null',
            'operator'=> null,
            'column'  => $column,
            'value'   => null,
            'boolean' => $boolean,
            'not' => $not,
        ];

        return $this;
    }

    public function orWhereNull(string $column): Builder
    {
        return $this->whereNull($column, 'or');
    }

    public function whereNotNull(string $column, string $boolean = 'and'): Builder
    {
        return $this->whereNull($column, $boolean, true);
    }

    public function orWhereNotNull(string $column): Builder
    {
        return $this->whereNotNull($column, 'or');
    }

    public function whereHas(string $column, Closure $callback, string $boolean = 'and', bool $not = false): Builder
    {
        $this->wheres[] = [
            'type' => 'type_has',
            'operator' => null,
            'column'  => $column,
            'value'   => $callback(new Builder($this->connection)),
            'boolean' => $boolean,
            'not' => $not,
        ];

        return $this;
    }

    public function whereDate(string $column, mixed $operator, mixed $value = null, string $boolean = 'and'): Builder
    {
        $this->wheres[] = [
            'type'    => 'date',
            'operator'=> $operator,
            'column'  => $column,
            'value'   => $value,
            'boolean' => $boolean,
            'not' => false,
        ];

        return $this;
    }

    public function orWhereBetween(string $column, iterable $values): Builder
    {
        return $this->whereBetween($column, $values, 'or');
    }

    public function whereNotBetween(string $column, iterable $values, string $boolean = 'and'): Builder
    {
        return $this->whereBetween($column, $values, $boolean, true);
    }

    public function orWhereNotBetween(string $column, iterable $values): Builder
    {
        return $this->whereNotBetween($column, $values, 'or');
    }

    public function create(array $values): ?Model
    {
        $model = $this->getLoadedModel($values, true);
        $document = Document::load($model->toArray());

        if($document->create()) {
            $model->setId($document->id());
            $model->setRevision($document->rev());

            return $model;
        }

        return null;
    }

    public function toQuery(): array
    {
        return $this->query()->toQuery();
    }

    protected function query(): QueryFactory
    {
        return QueryFactory::load(WhereFactory::load($this->wheres));
    }

    public function get(): Collection
    {
        $builder = ClientBuilder::withConnection();

        return collect($builder->search($this->query()->toQuery()))
            ->map(function(array $document) {
                return $this->getLoadedModel(
                    $document,
                    false,
                    Arr::get($document, '_id'),
                    Arr::get($document, '_rev')
                );
            });
    }

    public function delete(?string $id = null): int
    {
        $deleted = 0;
        if (!is_null($id)) {
            Document::load(['_id' => $id])->delete();
        }

        $this->get()
            ->each(function(Document $doc) use (&$deleted) {
                $deleted += (int) $doc->delete();
            });

        return $deleted;
    }

    public function whereBetween(string $column, iterable $values, string $boolean = 'and', bool $not = false): Builder
    {
        $this->wheres[] = [
            'column' => $column,
            'type' => 'type_between',
            'boolean' => $boolean,
            'values' => $values,
            'not' => $not,
        ];

        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): Builder
    {
        $direction = strtolower($direction);

        if (in_array($direction, ['asc', 'desc'])) {
            $this->orders[$column] = $direction;
        }

        return $this;
    }

    public function orderByDesc(string $column): Builder
    {
        return $this->orderBy($column, 'desc');
    }

    public function latest(string $column = 'created_at'): Builder
    {
        return $this->orderBy($column, 'desc');
    }

    public function oldest(string $column = 'created_at'): Builder
    {
        return $this->orderBy($column, 'asc');
    }

    public function skip(int $value): Builder
    {
        return $this->offset($value);
    }

    public function offset(int $value): Builder
    {
        $this->offset = max(0, $value);

        return $this;
    }

    public function take(int $value): Builder
    {
        return $this->limit($value);
    }

    public function limit(?int $value): Builder
    {
        $this->limit = $value;

        return $this;
    }

    public function first(): mixed
    {
        return $this->get()->first();
    }

    public function last(): mixed
    {
        return $this->get()->last();
    }

    public function exists(): bool
    {
        return $this->first() !== null;
    }

    public function setModel(Model $model): Builder
    {
        $this->model = $model;

        return $this;
    }

    public function __call($method, $parameters): Builder
    {
        return call_user_func_array([$this, $method], $parameters);
    }

    private function getLoadedModel(array $document, bool $writeAccess, ?string $id = null, ?string $revision = null): Model
    {
        $model = match($writeAccess) {
            true => $this->loadWritableModel($this->getModel(), $document),
            false => $this->loadReadableModel($this->getModel(), $document)
        };

        if($id) {
            $model->setId($id);
        }

        if($revision) {
            $model->setRevision($revision);
        }

        return $model;
    }

    private function loadReadableModel(Model $model, array $document): Model
    {
        foreach (Arr::except($document, ['_id', '_rev']) as $index => $value) {
            $model->$index = $model->castAttribute($index, $value);
        }

        return $model;
    }

    private function loadWritableModel(Model $model, array $document): Model
    {
        foreach (Arr::except($document, ['_id', '_rev']) as $index => $value) {
            $model->$index = $model->setAttribute($index, $value);
        }

        return $model;
    }

    private function getModel(): Model
    {
        $class = get_class($this->model);
        return new $class();
    }
}
