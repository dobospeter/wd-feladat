<?php

declare(strict_types=1);

namespace Inventory\Device\Domain;

use Inventory\Shared\Domain\Guard;

final class Device
{
    public const NAME_MAX = 255;
    public const MANUFACTURER_MAX = 255;
    public const MODEL_MAX = 255;
    public const DESCRIPTION_MAX = 2000;

    private function __construct(
        private readonly DeviceId $id,
        private string $name,
        private string $manufacturer,
        private string $model,
        private ?string $description,
    ) {
    }

    public static function register(
        DeviceId $id,
        string $name,
        string $manufacturer,
        string $model,
        ?string $description,
    ): self {
        return new self(
            $id,
            Guard::text($name, 'name', self::NAME_MAX),
            Guard::text($manufacturer, 'manufacturer', self::MANUFACTURER_MAX),
            Guard::text($model, 'model', self::MODEL_MAX),
            Guard::optionalText($description, 'description', self::DESCRIPTION_MAX),
        );
    }

    public static function reconstitute(
        DeviceId $id,
        string $name,
        string $manufacturer,
        string $model,
        ?string $description,
    ): self {
        return new self($id, $name, $manufacturer, $model, $description);
    }

    public function revise(string $name, string $manufacturer, string $model, ?string $description): void
    {
        $this->name = Guard::text($name, 'name', self::NAME_MAX);
        $this->manufacturer = Guard::text($manufacturer, 'manufacturer', self::MANUFACTURER_MAX);
        $this->model = Guard::text($model, 'model', self::MODEL_MAX);
        $this->description = Guard::optionalText($description, 'description', self::DESCRIPTION_MAX);
    }

    public function id(): DeviceId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function manufacturer(): string
    {
        return $this->manufacturer;
    }

    public function model(): string
    {
        return $this->model;
    }

    public function description(): ?string
    {
        return $this->description;
    }
}
