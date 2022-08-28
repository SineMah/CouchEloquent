# CouchEloquent

Lightweight CouchDB wrapper for Laravel. CouchEloquent has a similar syntax like Eloquent but no dependencies to it.
Mango Query is used to process all requests.

## Installation
```
composer require sinemah/coucheloquent
```
## Configuration
Create a file named `couchdb.php` on your config folder or copy the included from vendor.
```php
<?php

return [
    'session' => env('COUCHDB_SESSION', 300),
    'username' => env('COUCHDB_USER'),
    'password' => env('COUCHDB_PASSWORD'),
    'database' => env('COUCHDB_DATABASE', 'laravel'),
    'url' => env('COUCHDB_URL', 'http://couchdb:5984'),
];
```

## Models
Extend your model with `Sinemah\CouchEloquent\Eloquent\Model`. That's it.
```php
<?php

namespace App\Models;

use Sinemah\CouchEloquent\Eloquent\Model;

class Item extends Model
{
    protected $casts = [
        'name' => 'json',
    ];
} 
```

### Supported methods (model instance)
* count
* find
* save
* update
* delete
* get
* toQuery
* where
* orWhere
* whereNot
* whereIn
* whereNull
* whereHas
* whereBetween
* first
* last
* exists

### Supported casts
* int
* real
* float
* double 
* decimal
* string
* bool
* object
* array
* json
* collection

## Usage
### Create document
```php
use Ramsey\Uuid\Uuid;

$doc = Pet::create([
    'name' => [
        'first_name' => 'Chico',
        'last_name' => 'Mr.'
    ],
    'treatments' => [
        [
            'id' => Uuid::uuid6(),
            'random_int' => rand(0, 10),
        ]
    ],
    'gender' => 'male',
    'breed' => 'Mastin-Mix'
]); 
```

### Update document
```php
$pet = $pets->first();
$pet->name = [
    'first_name' => 'chico',
    'last_name' => 'MR.'
];

$pet->save();
```

### Query Builder

```php
 $pets = Pet::query()
    ->where('breed', 'Mastin-Mix')
    ->whereIn('breed', ['Mastin-Mix'])
    ->whereBetween('created_at.values.year', [2022, 2023])
    ->where(function(Builder $query) {
        $query->orWhere('gender', 'male');
        $query->orWhere('gender', 'female');

        return $query;
    })
    ->whereHas('treatments', function(Builder $query) {
        $query->where('random_int', 1);

        return $query;
    })
    ->get();
```