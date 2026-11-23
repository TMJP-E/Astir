<?php

final class Validator
{
    private function __construct()
    {
    }

    public static function validateRequiredString(string $property, mixed $value, int $maxLength): void
    {
        if (!is_string($value)) {
            throw new RequiredValueException($property, "Este campo es obligatorio.");
        }

        if (trim($value) === "") {
            throw new RequiredValueException($property, "Este campo debe contener texto.");
        }

        if (mb_strlen($value, "UTF-8") > $maxLength) {
            throw new StringLengthException($property, "No puede superar los $maxLength caracteres.");
        }
    }

    public static function validateUuid(string $property, mixed $value): void
    {
        if (!is_string($value) || !preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[1-8][a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/iD', $value)) {
            throw new InvalidUuidException($property, "Debe contener un identificador válido.");
        }

    }

    public static function validateEnum(string $property, mixed $value, array $allowedValues): void
    {
        if (!is_string($value) || !in_array($value, $allowedValues, true)) {
            throw new InvalidEnumException($property, "El valor seleccionado no está permitido.");
        }
    }

    public static function validateInteger(string $property, mixed $value, int $minimum): void
    {
        if (!is_int($value)) {
            throw new NumericConstraintException($property, "Debe ser un número entero.");
        }

        if ($value < $minimum) {
            throw new NumericConstraintException($property, "Debe ser mayor o igual que {$minimum}.");
        }
        if ($value > 2147483647) {
            throw new NumericConstraintException($property, "No puede superar 2147483647.");
        }
    }

    public static function validatePrice(string $property, mixed $value): void
    {
        if ((!is_int($value) && !is_float($value)) || !is_finite((float) $value)) {
            throw new NumericConstraintException($property, "Debe ser un número válido.");
        }

        if ($value < 0 || $value > 999999999.99) {
            throw new NumericConstraintException($property, "Debe estar entre 0 y 999999999.99.");
        }

        if (round((float) $value, 2) != (float) $value) {
            throw new NumericConstraintException($property, "No puede tener más de dos decimales.");
        }
    }

    public static function validateEmail(string $property, mixed $value): void
    {
        self::validateRequiredString($property, $value, 96);

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException($property, "Debe contener un correo electrónico válido.");
        }
    }

    public static function validateStringArray(string $property, mixed $value): void
    {
        if (!is_array($value)) {
            throw new InvalidArrayException($property, "Debe ser una lista de textos.");
        }

        foreach ($value as $element) {
            if (!is_string($element)) {
                throw new InvalidArrayException($property, "Todos los elementos deben ser textos.");
            }
        }
    }
}
