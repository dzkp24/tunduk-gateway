<?php

namespace App\Domain\DTO\SocFund;

use Symfony\Component\Validator\Constraints as Assert;

class SocFundInfoRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 14, max: 14)]
        public string $pin,

        public ?string $requestOrg = null,
        public ?string $requestPerson = null,
    ) {}
}
