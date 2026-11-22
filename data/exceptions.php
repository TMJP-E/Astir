<?php

class ValidationException extends InvalidArgumentException
{
    protected string $property;

    public function __construct(string $property, string $message)
    {
        $this->property = $property;
        parent::__construct("$property: $message");
    }

    public function getProperty(): string
    {
        return $this->property;
    }
}

class RequiredValueException extends ValidationException
{
}
class StringLengthException extends ValidationException
{
}
class InvalidEnumException extends ValidationException
{
}
class NumericConstraintException extends ValidationException
{
}
class InvalidUuidException extends ValidationException
{
}
class InvalidArrayException extends ValidationException
{
}
class InvalidEmailException extends ValidationException
{
}
class MissingSpecialSymbolException extends ValidationException
{
}
class MissingNumberException extends ValidationException
{
}
class MissingUppercaseException extends ValidationException
{
}
class MissingLowercaseException extends ValidationException
{
}
class UnrecognizedUserException extends ValidationException
{
}
class InvalidImageException extends ValidationException
{
}