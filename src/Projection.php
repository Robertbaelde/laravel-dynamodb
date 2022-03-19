<?php

namespace Robertbaelde\LaravelDynamodb;

class Projection
{
    public function __construct(
        public readonly ProjectionType $projectionType,
        public readonly array $nonKeyAttributes = []
    ) {
    }

    public static function all(): self
    {
        return new self(ProjectionType::All, []);
    }

    public function toApi(): array
    {
        if ($this->projectionType !== ProjectionType::Include) {
            return [
                'ProjectionType' => $this->projectionType->value,
            ];
        }
        return [
            'ProjectionType' => $this->projectionType->value,
            'NonKeyAttributes' => $this->nonKeyAttributes
        ];
    }
}
