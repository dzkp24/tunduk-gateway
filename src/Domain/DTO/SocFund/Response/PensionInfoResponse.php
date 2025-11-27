<?php

namespace App\Domain\DTO\SocFund\Response;

class PensionInfoResponse
{
    public function __construct(
        public string $causeOfError, // OK, BAD_REQUEST, INTERNAL_ERROR
        /** @var PinInfo[] */
        public array $pinInfos = [],
        /** @var DossierInfo[] */
        public array $dossiers = [],
    ) {}

    public function isSuccess(): bool
    {
        return $this->causeOfError === 'OK';
    }
}
