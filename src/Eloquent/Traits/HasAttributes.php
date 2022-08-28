<?php

namespace Sinemah\CouchEloquent\Eloquent\Traits;

use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Date;

trait HasAttributes
{
    protected $casts = [];

    /**
     * The built-in, primitive cast types supported by Eloquent.
     *
     * @var string[]
     */
    protected static $primitiveCastTypes = [
        'array',
        'bool',
        'boolean',
        'date',
        'datetime',
        'decimal',
        'double',
        'encrypted',
        'float',
        'int',
        'integer',
        'json',
        'object',
        'real',
        'string',
        'timestamp',
    ];

    protected string $dateFormat;

    public function __set(string $name, mixed $value): void
    {
        $this->original[$name] = $value;
        $this->attributes[$name] = $value;
    }

    public function __get($name): mixed
    {
        if(array_key_exists($name, $this->attributes)) {
//            return $this->setAttribute($name, $this->attributes[$name] ?? null);
            return $this->attributes[$name];
        }

        return null;
    }

    public function getCasts(): array
    {
        return $this->casts;
    }

    protected function getCastType(string $key): ?string
    {
        return Arr::get($this->casts, $key);
    }

    public function castAttribute(string $key, mixed $value): mixed
    {
        if($castType = $this->getCastType($key)) {
            $value = match($castType) {
                'int', 'integer' => (int) $value,
                'real', 'float', 'double' => $this->fromFloat($value),
                'decimal' => $this->asDecimal($value, explode(':', $this->getCasts()[$key], 2)[1]),
                'string' => (string) $value,
                'bool', 'boolean' => (bool) $value,
                'object' => $this->fromJson($value, true),
                'array', 'json' => $this->fromJson($value),
                'collection' => new Collection($this->fromJson($value)),
                'date' => $this->asDate($value),
                'datetime' => $this->asDateTime($value),
                'timestamp' => $this->asTimestamp($value),
                default => $value
            };
        }

        $this->attributes[$key] = $value;

        return $value;
    }

    public function setAttribute(string $key, mixed $value): mixed
    {
        if($castType = $this->getCastType($key)) {
            $value = match($castType) {
                'int', 'integer', 'timestamp' => (int) $value,
                'real', 'float', 'double' => $this->fromFloat($value),
                'string', 'decimal' => (string) $value,
                'bool', 'boolean' => (bool) $value,
                'object', 'array', 'json' => $this->toJson($value),
                'collection' => $this->fromCollection($value),
                'date', 'datetime' => $this->fromDate($value),
                default => $value
            };
        }

        return $value;
    }

    public function fromFloat(mixed $value): float
    {
        return match ((string) $value) {
            'Infinity' => INF,
            '-Infinity' => -INF,
            'NaN' => NAN,
            default => (float) $value,
        };
    }

    protected function asDecimal($value, $decimals)
    {
        return number_format($value, $decimals, '.', '');
    }

    public function fromJson(mixed $value, $asObject = false): object|array|null
    {
        return json_decode($value, !$asObject);
    }

    public function fromCollection(Collection $value): string
    {
        return json_encode($value->toArray());
    }

    public function toJson(mixed $value): string
    {
        return json_encode($value);
    }

    protected function fromDate(Carbon $value): string
    {
        return $value->toDateString();
    }

    protected function asDate(mixed $value): Carbon
    {
        return $this->asDateTime($value)->startOfDay();
    }

    protected function asDateTime(mixed $value): Carbon
    {
        $date = match (true) {
            $value instanceof CarbonInterface => Date::instance($value),
            $value instanceof DateTimeInterface => Date::parse($value->format('Y-m-d H:i:s.u'), $value->getTimezone()),
            is_numeric($value) => Date::createFromTimestamp($value),
            $this->isStandardDateFormat($value) => Date::instance(Carbon::createFromFormat('Y-m-d', $value)->startOfDay()),
            default => Date::createFromFormat($this->getDateFormat(), $value)
        };

        return $date ?: Date::parse($value);
    }

    protected function getDateFormat(): string
    {
        return $this->dateFormat ?: 'Y-m-d H:i:s';
    }

    protected function isStandardDateFormat(string $value): bool
    {
        return preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value);
    }

    public function fromDateTime(mixed $value): ?string
    {
        return empty($value) ? $value : $this->asDateTime($value)->format(
            $this->getDateFormat()
        );
    }

    public function fromEncryptedString(string $value)
    {
        return (static::$encrypter ?? Crypt::getFacadeRoot())->decrypt($value, false);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function toCastedArray(): array
    {
        $attributes = [];

        foreach($this->toArray() as $index => $value) {
            $attributes[$index] = $this->setAttribute($index, $value);
        }
        return $attributes;
    }

    protected function asTimestamp(mixed $value): int
    {
        return $this->asDateTime($value)->getTimestamp();
    }
}
