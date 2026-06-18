<?php
use PHPUnit\Framework\TestCase;

class PasswordValidationTest extends TestCase
{
    private function validar_password($password) {
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

    public function test_acepta_password_valida()
    {
        $resultado = $this->validar_password('Eduka2026@');
        $this->assertEquals('', $resultado);
    }

    public function test_rechaza_password_muy_corta()
    {
        $resultado = $this->validar_password('Ab1@');
        $this->assertStringContainsString('8 caracteres', $resultado);
    }

    public function test_rechaza_password_sin_mayuscula()
    {
        $resultado = $this->validar_password('eduka2026@');
        $this->assertStringContainsString('mayúscula', $resultado);
    }

    public function test_rechaza_password_sin_minuscula()
    {
        $resultado = $this->validar_password('EDUKA2026@');
        $this->assertStringContainsString('minúscula', $resultado);
    }

    public function test_rechaza_password_sin_numero()
    {
        $resultado = $this->validar_password('EdukaTest@');
        $this->assertStringContainsString('número', $resultado);
    }

    public function test_rechaza_password_sin_caracter_especial()
    {
        $resultado = $this->validar_password('Eduka2026');
        $this->assertStringContainsString('carácter especial', $resultado);
    }
}