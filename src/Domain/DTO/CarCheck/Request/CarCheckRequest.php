<?php

namespace App\Domain\DTO\CarCheck\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CarCheckRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $govPlate,
    ) {}
}
