<?php

namespace App\Domain\DTO\SocFund\Response;

class PinInfo
{
    public function __construct(
        public int $state,
        public string $pin,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $fullName,
        public ?string $data,
        public ?string $issuer,
    ) {}
}
