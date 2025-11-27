<?php

namespace App\Infrastructure\Soap;

use App\Domain\Interfaces\SoapClientInterface;
use Exception;
use Psr\Log\LoggerInterface;
use SoapClient;
use SoapFault;

class XRoadSoapClient implements SoapClientInterface
{
    private SoapClient $client;

    /**
     * @throws SoapFault
     */
    public function __construct(
        string                              $wsdl,
        private readonly string             $location,
        private readonly string             $serviceSubSystem,
        private readonly string             $serviceCode,
        private readonly string             $memberCode,
        private readonly string             $serviceVersion,
        private readonly XRoadHeaderFactory $headers,
        private readonly LoggerInterface    $logger,
    ) {
        $this->logger->debug('start');
        $this->client = new SoapClient($wsdl, [
            'location' => $this->location,
            'trace' => true,
            'exception' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'stream_context' => stream_context_create([
                'http' => [
                    'timeout' => 5
                ]
            ]),
        ]);
        dd($wsdl, $this->location, $this->serviceSubSystem, $this->serviceCode, $this->memberCode, $this->serviceVersion);

        $this->logger->debug('end');
    }

    /**
     * @throws SoapFault
     */
    public function call(string $method, array $params = []): mixed
    {
        $this->logger->info(sprintf('Tunduk Request [%s]', $method), [
            'params' => $params,
        ]);

        try {
            $headers = $this->headers->make(
                $this->serviceSubSystem,
                $this->serviceCode,
                $this->memberCode,
                $this->serviceVersion,
            );

            $this->client->__setSoapHeaders($headers);

            return $this->client->__soapCall($method, !empty($params) ? $params : []);
        } catch (Exception $e) {
            $this->logger->error(sprintf('Tunduk Error [%s]: %s', $method, $e->getMessage()), [
                'trace' => $e->getTraceAsString(),
                'last_request' => $this->client->__getLastRequest(),
                'last_response' => $this->client->__getLastResponse(),
            ]);

            throw $e;
        }
    }
}
