<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../backend/helpers/jwt.php';

class JwtTest extends TestCase
{
    public function test_genera_un_token_con_tres_partes()
    {
        $token = jwt_generar(['usuario_id' => 1, 'nombre' => 'Juan', 'rol' => 'estudiante']);
        $partes = explode('.', $token);

        $this->assertCount(3, $partes);
    }

    public function test_verifica_un_token_valido_correctamente()
    {
        $token = jwt_generar(['usuario_id' => 5, 'nombre' => 'Ana', 'rol' => 'administrador']);
        $payload = jwt_verificar($token);

        $this->assertNotFalse($payload);
        $this->assertEquals(5, $payload['usuario_id']);
        $this->assertEquals('Ana', $payload['nombre']);
        $this->assertEquals('administrador', $payload['rol']);
    }

    public function test_rechaza_un_token_modificado()
    {
        $token = jwt_generar(['usuario_id' => 1, 'nombre' => 'Juan', 'rol' => 'estudiante']);

        // Modificamos un carácter del token para simular manipulación
        $token_modificado = substr($token, 0, -5) . 'XXXXX';

        $payload = jwt_verificar($token_modificado);

        $this->assertFalse($payload);
    }

    public function test_rechaza_un_token_con_formato_invalido()
    {
        $payload = jwt_verificar('esto-no-es-un-token-valido');

        $this->assertFalse($payload);
    }

    public function test_rechaza_un_token_expirado()
    {
        // Generamos un token y luego forzamos su expiración manualmente
        define('JWT_SECRET_TEST', 'eduka_clave_secreta_2026_cambiar_en_produccion');

        $header  = base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload_data = ['usuario_id' => 1, 'nombre' => 'Juan', 'rol' => 'estudiante', 'iat' => time() - 7200, 'exp' => time() - 3600];
        $payload = base64url_encode(json_encode($payload_data));
        $firma   = base64url_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));

        $token_expirado = "$header.$payload.$firma";

        $resultado = jwt_verificar($token_expirado);

        $this->assertFalse($resultado);
    }
}