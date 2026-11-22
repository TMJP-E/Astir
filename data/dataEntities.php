<?php

include_once(__DIR__ . "/exceptions.php");
include_once(__DIR__ . "/dataValidation.php");

class User
{
    private ?string $uuid = null;
    private string $name;
    private string $email;
    private string $password;
    private string $role = "regular";

    public function __construct(mixed $uuid, mixed $name, mixed $email, mixed $password, mixed $role = "regular")
    {
        if ($uuid !== null) {
            $this->setUuid($uuid);
        }
        $this->setName($name);
        $this->setEmail($email);
        $this->setPassword($password);
        $this->setRole($role);
    }


    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    private function setUuid(mixed $uuid): void
    {
        Validator::validateUuid("Identificador de usuario", $uuid);
        $this->uuid = $uuid;
    }

    public function setName(mixed $name): void
    {
        Validator::validateRequiredString("Nombre", $name, 48);
        $this->name = $name;
    }

    public function setEmail(mixed $email): void
    {
        Validator::validateEmail("Correo electrónico", $email);
        $this->email = $email;
    }

    public function setPassword(mixed $password): void
    {
        Validator::validateRequiredString("Contraseña", $password, 255);
        $this->password = $password;
    }

    public function setRole(mixed $role): void
    {
        Validator::validateEnum("Rol", $role, ["regular", "admin"]);
        $this->role = $role;
    }
}

class Room
{
    private ?string $uuid = null;
    private string $name;
    private string $description;
    private string $category;
    private float $price;
    private int $available;
    private array $commodities;
    private array $images;

    public function __construct(
        mixed $uuid,
        mixed $name,
        mixed $description,
        mixed $category,
        mixed $price,
        mixed $available,
        array $commodities = [],
        array $images = []
    ) {
        if ($uuid !== null) {
            $this->setUuid($uuid);
        }
        $this->setName($name);
        $this->setDescription($description);
        $this->setCategory($category);
        $this->setPrice($price);
        $this->setAvailable($available);
        $this->setCommodities($commodities);
        $this->setImages($images);
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getAvailable(): int
    {
        return $this->available;
    }

    public function getCommodities(): array
    {
        return $this->commodities;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    private function setUuid(mixed $uuid): void
    {
        Validator::validateUuid("Identificador de habitación", $uuid);
        $this->uuid = $uuid;
    }

    public function setName(mixed $name): void
    {
        Validator::validateRequiredString("Nombre", $name, 255);
        $this->name = $name;
    }

    public function setDescription(mixed $description): void
    {
        Validator::validateRequiredString("Descripción", $description, 4095);
        $this->description = $description;
    }

    public function setCategory(mixed $category): void
    {
        Validator::validateEnum("Categoría", $category, ["express", "regular", "comfort", "suite"]);
        $this->category = $category;
    }

    public function setPrice(mixed $price): void
    {
        Validator::validatePrice("Precio", $price);
        $this->price = $price;
    }

    public function setAvailable(mixed $available): void
    {
        Validator::validateInteger("Disponibilidad", $available, 0);
        $this->available = $available;
    }

    public function setCommodities(mixed $commodities): void
    {
        Validator::validateStringArray("Comodidades", $commodities);
        $this->commodities = $commodities;
    }

    public function setImages(mixed $images): void
    {
        Validator::validateStringArray("Imágenes", $images);
        $this->images = $images;
    }
}


class Commodity
{
    private string $type;

    public function __construct(mixed $type)
    {
        $this->setType($type);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(mixed $type): void
    {
        Validator::validateRequiredString("Tipo", $type, 64);
        $this->type = $type;
    }
}

class UserRoom
{
    private string $userUuid;
    private string $roomUuid;
    private int $quantity;
    private string $userName;
    private string $roomName;

    public function __construct(mixed $userUuid, mixed $roomUuid, mixed $quantity, mixed $userName, mixed $roomName)
    {
        $this->setUserUuid($userUuid);
        $this->setRoomUuid($roomUuid);
        $this->setQuantity($quantity);
        $this->setUserName($userName);
        $this->setRoomName($roomName);
    }

    public function getUserUuid(): string
    {
        return $this->userUuid;
    }

    public function getRoomUuid(): string
    {
        return $this->roomUuid;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function getRoomName(): string
    {
        return $this->roomName;
    }

    public function setUserUuid(mixed $userUuid): void
    {
        Validator::validateUuid("Identificador del usuario", $userUuid);
        $this->userUuid = $userUuid;
    }

    public function setRoomUuid(mixed $roomUuid): void
    {
        Validator::validateUuid("Identificador de la habitación", $roomUuid);
        $this->roomUuid = $roomUuid;
    }

    public function setQuantity(mixed $quantity): void
    {
        Validator::validateInteger("Cantidad", $quantity, 1);
        $this->quantity = $quantity;
    }

    public function setUserName(mixed $userName): void
    {
        Validator::validateRequiredString("Nombre del usuario", $userName, 48);
        $this->userName = $userName;
    }

    public function setRoomName(mixed $roomName): void
    {
        Validator::validateRequiredString("Nombre de la habitación", $roomName, 255);
        $this->roomName = $roomName;
    }
}
