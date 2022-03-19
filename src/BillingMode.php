<?php

namespace Robertbaelde\LaravelDynamodb;

enum BillingMode: string
{
    case Provisioned = 'PROVISIONED';
    case PayPerRequest = 'PAY_PER_REQUEST';
}
