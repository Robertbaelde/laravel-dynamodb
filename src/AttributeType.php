<?php

namespace Robertbaelde\LaravelDynamodb;

enum AttributeType: string
{
    case String = 'S';
    case Number = 'N';
    case Binary = 'B';
}
