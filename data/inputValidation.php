<?php

final class InputValidator
{
    private function __construct()
    {
    }

    public static function validatePassword(mixed $password): void
    {
        if (!is_string($password) || mb_strlen($password, "UTF-8") < 12) {
            throw new StringLengthException("Contraseña", "Debe contener al menos 12 caracteres.");
        }

        if (!preg_match("/[^a-zA-Z0-9]/", $password)) {
            throw new MissingSpecialSymbolException("Contraseña", "Debe contener al menos un símbolo especial.");
        }

        if (!preg_match("/[0-9]/", $password)) {
            throw new MissingNumberException("Contraseña", "Debe contener al menos un número.");
        }

        if (!preg_match("/[A-Z]/", $password)) {
            throw new MissingUppercaseException("Contraseña", "Debe contener al menos una letra mayúscula.");
        }

        if (!preg_match("/[a-z]/", $password)) {
            throw new MissingLowercaseException("Contraseña", "Debe contener al menos una letra minúscula.");
        }
    }

    public static function validateImage(mixed $image): string
    {
        if (!is_array($image)) {
            throw new InvalidImageException("Imagen", "El archivo enviado no es válido.");
        }

        if (!isset($image["error"]) || $image["error"] !== UPLOAD_ERR_OK) {
            throw new InvalidImageException("Imagen", "No fue posible subir la imagen correctamente.");
        }

        if (!isset($image["size"]) || !is_int($image["size"]) || $image["size"] > 8 * 1024 * 1024) {
            throw new InvalidImageException("Imagen", "No puede superar los 8 MB.");
        }

        if (!isset($image["tmp_name"]) || !is_string($image["tmp_name"]) || !is_uploaded_file($image["tmp_name"])) {
            throw new InvalidImageException("Imagen", "El archivo enviado no es una imagen válida.");
        }

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($image["tmp_name"]);

        $allowedMimeTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new InvalidImageException("Imagen", "El formato debe ser JPEG, PNG, GIF o WebP.");
        }

        if (getimagesize($image["tmp_name"]) === false) {
            throw new InvalidImageException("Imagen", "El archivo no contiene una imagen válida.");
        }

        return $mimeType;

    }
}