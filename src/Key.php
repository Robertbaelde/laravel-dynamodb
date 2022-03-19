<?php

namespace Robertbaelde\LaravelDynamodb;

class Key
{
    public function __construct(
        public readonly string $attributeName,
        public readonly AttributeType $attributeType
    ) {
    }

    public static function string(string $attributeName): self
    {
        return new self($attributeName, AttributeType::String);
    }

    public function toAttributeDefinition(): array
    {
        return [
            'AttributeName' => $this->attributeName,
            'AttributeType' => $this->attributeType->value,
        ];
    }
}
