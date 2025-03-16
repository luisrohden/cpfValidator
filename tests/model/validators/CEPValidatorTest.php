<?php

namespace tests\model\validators;

use model\validators\CEPValidator;

use PHPUnit\Framework\TestCase;
use Exception;

class CEPValidatorTest extends TestCase
{
    private string $apiUrl = 'https://viacep.com.br/ws/';

    public function testValidCEP()
    {
        $cep = '03361010'; // CEP válido e cadastrado
        $validator = new CEPValidator($cep, $this->apiUrl);

        $data = $validator->getCEPData('json');

        $this->assertIsArray($data);
        $this->assertEquals('03361-010', $data['cep']);
        $this->assertEquals('Avenida Cipriano Rodrigues', $data['logradouro']);
    }

    public function testValidButUnregisteredCEP()
    {
        $cep = '00000000'; // CEP válido, mas não cadastrado

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('CEP válido, mas não cadastrado nos Correios.');

        $validator = new CEPValidator($cep, $this->apiUrl);
        $validator->getCEPData('json');
    }

    public function testInvalidCEP()
    {
        $cep = '123'; // CEP inválido

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('CEP inválido. O CEP deve conter exatamente 8 dígitos.');

        $validator = new CEPValidator($cep, $this->apiUrl);
        $validator->getCEPData('json');
    }

    public function testInvalidReturnType()
    {
        $cep = '01001000'; // CEP válido

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Tipo de retorno inválido. Use \'json\' ou \'xml\'.');

        $validator = new CEPValidator($cep, $this->apiUrl);
        $validator->getCEPData('invalid'); // Tipo de retorno inválido
    }

    
}