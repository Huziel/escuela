<?php

namespace App\DTO;

use Illuminate\Support\Arr;

abstract class BaseDTO
{
    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    public function fill(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function only(array $keys): array
    {
        return Arr::only($this->toArray(), $keys);
    }

    public function except(array $keys): array
    {
        return Arr::except($this->toArray(), $keys);
    }

    public static function fromRequest(array $data): static
    {
        return new static($data);
    }
}
