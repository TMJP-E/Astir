<?php
include_once(__DIR__ . "/../data/dataEntities.php");
include_once(__DIR__ . "/../data/inputValidation.php");

$lock = true;
if ($lock) {
    header("Location:/urbano/");
    die();
}

$passwords = [
    "UrbanoDemo2026!",
    "HotelPrueba2026#",
    "ReservaDemo2026$",
    "Habitacion2026@A",
    "ClientePrueba26!",
    "AdminUrbano2026#",
    "ViajeEmpresa26$",
    "EstanciaHotel26@",
    "RecepcionDemo26!",
    "GerenciaHotel26#",
    "NegocioPrueba26$",
    "AccesoUrbano26@",
    "ReservaHotel26!",
    "UsuarioDemo2026#",
    "SuitePrueba2026$",
    "TrabajoHotel26@"
];


foreach ($passwords as $password) {
    try {
        InputValidator::validatePassword($password);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        if (!is_string($hashedPassword)) {
            throw new RuntimeException("No fue posible generar el hash de la contraseña.");
        }
        echo $hashedPassword . "\n";
    } catch (ValidationException $exception) {
        fwrite(STDERR, "La contraseña de inicialización no es válida: {$exception->getMessage()}" . PHP_EOL);
        exit(1);
    }
}
