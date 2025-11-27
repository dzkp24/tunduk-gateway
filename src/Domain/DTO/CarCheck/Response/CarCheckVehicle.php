<?php

namespace App\Domain\DTO\CarCheck\Response;

use DateTimeImmutable;

readonly class CarCheckVehicle
{
    public function __construct(
        public string             $govPlate,
        public ?string            $brand = null,
        public ?string            $model = null,
        public ?int               $year = null,
        public ?string            $color = null,
        public ?string            $steering = null,     // "слева", "справа"
        public ?int               $engineVolume = null, // см3
        public ?string            $fuelType = null,     // motorType (бензин, дизель)

        // Особые отметки (ГБО, переоборудование и т.д.)
        public ?string            $specialNotes = null,

        // Дата окончания разрешения на тонировку (если null - разрешения нет)
        public ?DateTimeImmutable $tintingValidUntil = null
    ) {}
}
