<?php

declare(strict_types = 1);

namespace Graphpinator\Parser;

enum OperationType : string
{
    case QUERY = 'query';
    case MUTATION = 'mutation';
    case SUBSCRIPTION = 'subscription';
}
