<?php

namespace Robertbaelde\LaravelDynamodb;

enum KeyType: string
{
    case PARTITION = 'HASH';
    case SORT = 'RANGE';
}
