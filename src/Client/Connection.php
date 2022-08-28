<?php

namespace Sinemah\CouchEloquent\Client;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Sinemah\CouchEloquent\Exceptions\CouchDbAuthException;

class Connection
{
    /**
     * @throws CouchDbAuthException
     */
    public function session(): ?string
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post(config('couchdb.url') . '/_session', [
            'name' => config('couchdb.username'),
            'password' => config('couchdb.password'),
        ]);

        if($response->status() === 200) {
                return $response->header('Set-Cookie') ?? null;
        }

        throw new CouchDbAuthException();
    }

    public function get(): ?string
    {
        return Cache::remember('couch_session_duration', config('couchdb.session'), function() {
            return $this->session();
        });
    }
}