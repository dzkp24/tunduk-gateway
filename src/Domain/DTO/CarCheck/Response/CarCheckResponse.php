<?php

namespace App\Domain\DTO\CarCheck\Response;

class CarCheckResponse
{
    public function __construct(
        // Статус ответа (200, 203 - не найдено, 100 - ошибка и т.д.)
        public int              $status,

        // Данные об автомобиле
        public ?CarCheckVehicle $vehicle = null,

        // Флаги (только для carCheckPaid / ByGovPlate)
        public bool             $hasArrest = false,  // Есть ли арест/залог
        public bool             $hasPenalty = false, // (опционально, если будете совмещать с Violation сервисом)

        // Детали (только для платной версии)
        public ?int             $mileage = null, // Пробег в км

        /** @var string[] Список URL или Base64 картинок */
        public array            $photos = [],

        /** @var CarCheckHistory[] История регистраций */
        public array            $history = [],

        public bool             $isPaidReportAvailable = false,
        public ?string          $paymentCode = null
    ) {}

    public function isFound(): bool
    {
        return $this->status === 200 && $this->vehicle !== null;
    }
}
