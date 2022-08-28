<?php

namespace Sinemah\CouchEloquent\Types;


use Illuminate\Support\Carbon;

class Date
{
    public static function load(Carbon $date): Date
    {
        return new self($date);
    }

    private function __construct(private readonly Carbon $date)
    {
    }

    public function toArray(): array
    {
        return [
            'datetime' => $this->date->toISOString(),
            'timestamp' => $this->date->getTimestamp(),
            'values' => [
                'year' => $this->date->year,
                'month' => $this->date->month,
                'day' => $this->date->day,
                'hour' => $this->date->hour,
                'minute' => $this->date->minute,
                'second' => $this->date->second,
                'timezone' => $this->date->timezone->getName(),
            ]
        ];
    }
}