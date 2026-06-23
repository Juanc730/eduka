<?php
/**
 * Valida que una contraseña cumpla con los requisitos de seguridad establecidos:
 * mínimo 8 caracteres, al menos una mayúscula, una minúscula, un número
 * y un carácter especial.
 *
 * @param string $password Contraseña a validar
 * @return string Mensaje de error si la validación falla, o cadena vacía si es válida
 */
function validar_password($password) {
    if (strlen($password) < 8)
        return 'La contraseña debe tener al menos 8 caracteres.';
    if (!preg_match('/[A-Z]/', $password))
        return 'La contraseña debe tener al menos una letra mayúscula.';
    if (!preg_match('/[a-z]/', $password))
        return 'La contraseña debe tener al menos una letra minúscula.';
    if (!preg_match('/[0-9]/', $password))
        return 'La contraseña debe tener al menos un número.';
    if (!preg_match('/[\@\#\$\%\^\&\*\!\?\.\,\-\_]/', $password))
        return 'La contraseña debe tener al menos un carácter especial.';
    return '';
}
?>