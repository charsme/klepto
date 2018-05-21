<?php

namespace Klepto;

use Psr\Log\LoggerAwareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * ReducerFactory
 */
class ReducerFactory implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    
    public function create(array $uses = [])
    {
    }
}
