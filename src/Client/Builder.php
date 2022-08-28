<?php

namespace Sinemah\CouchEloquent\Client;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Sinemah\CouchEloquent\Types\Date;

class Builder {

    private function __construct(private readonly Connection $connection)
    {
    }

    public static function withConnection(): Builder
    {
        return self::load(new Connection());
    }

    public static function load(Connection $connection): Builder
    {
        return new self($connection);
    }

    public function find(string $id): ?array
    {
        $response = $this->request()->get($this->database($id));

        if($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function search(?array $params=null): ?array
    {
        $response = match (true) {
            $params === null => $this->findAll(),
            is_array($params) => $this->request()->post($this->database('_find'), $params),
        };

        if($response->successful()) {
            return $response->json('docs');
        }

        return null;
    }

    public function findAll(): Response
    {
        return $this->request()->post($this->database('_find'), [
            'selector' => [
                '$and' => [
                    [
                        '_id' => [
                            '$gt' => null
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function create(array $values): ?array
    {
        $date = Date::load(now());
        $timestamps = [
            'created_at' => $date->toArray(),
            'updated_at' => $date->toArray(),
            'deleted_at' => null,
        ];
        $response = $this->request()->put(
            $this->database($values['id'] ?? Str::uuid()),
            array_merge(
                Arr::except($values, ['id']),
                $timestamps,
            )
        );

        if($response->json('ok')) {
            return array_merge(
                [
                    '_id' => $response->json('id'),
                    '_rev' => $response->json('rev'),
                ],
                $values,
                $timestamps,
            );
        }

        return null;
    }

    public function update(string $id, array $values): ?array
    {
        $date = Date::load(now());
        $document = $this->find($id);
        $timestamps = [
            'created_at' => $document['created_at'] ?? $date->toArray(),
            'updated_at' => $date->toArray(),
            'deleted_at' => null,
        ];

        $values['_rev'] = $document['_rev'];
        $values = array_merge(
            $values,
            ['_rev' => $document['_rev']],
            $timestamps
        );

        $response = $this->request()->put($this->database($id), $values);

        if($response->json('ok')) {
            return array_merge(
                [
                    '_id' => $response->json('_id'),
                    '_rev' => $response->json('_rev'),
                ],
                $values,
                $timestamps
            );
        }

        return null;
    }

    public function delete(string $id): bool
    {
        $document = $this->find($id);

        $response = $this->request()->delete($this->database($id) . '?' . Arr::query(['rev' => $document['_rev'] ?? null]));

        return $response->json('ok', false);
    }

    public function softDelete(string $id): bool
    {
        $date = Date::load(now());
        $document = $this->find($id);
        $timestamps = [
            'created_at' => $document['created_at'] ?? $date->toArray(),
            'updated_at' => $document['updated_at'] ?? $date->toArray(),
            'deleted_at' => $date->toArray(),
        ];

        $response = $this->request()->put(
            $this->database($id),
            array_merge(
                $document,
                $timestamps,
            )
        );


        return $response->json('ok', false);
    }

    public function database(?string $endpoint=null): string
    {
        $idLength = strlen((string) $endpoint);
        $count = (int) round($idLength/($idLength + 1));

        return collect(array_slice(
            [config('couchdb.url'), config('couchdb.database'), $endpoint],
            0,
            2 + $count
        ))->implode('/');
    }

    public function request(): PendingRequest
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Cookie' => $this->connection->get(),
        ]);
    }

    protected function getRows(Response $response, mixed $params): ?array
    {
        $key = 'rows';


        if(is_array($params)) {
            $key = 'docs';
        }

        return $response->json($key);
    }
}
