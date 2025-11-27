<?php

namespace App\Domain\Interfaces;

interface SoapClientInterface
{
    /**
     * Make a SOAP call to the service.
     *
     * @param string $method SOAP Method
     * @param array $params Request parameters
     *
     * @return mixed
     */
    public function call(string $method, array $params = []): mixed;
}
