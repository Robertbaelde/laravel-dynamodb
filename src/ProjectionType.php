<?php

namespace Robertbaelde\LaravelDynamodb;

enum ProjectionType: string
{
    case KeysOnly = 'KEYS_ONLY';
    case Include = 'INCLUDE';
    case All = 'ALL';
}
