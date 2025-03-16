<?php
require __DIR__ . './vendor/autoload.php';
use model\validators\CEPValidator;

try {
    $cep = '00000000'; // CEP inválido ou não cadastrado
    $apiUrl = 'https://viacep.com.br/ws'; // URL da API
    $cepValidator = new CEPValidator($cep, $apiUrl);
    $cepData = $cepValidator->getCEPData('json');
    header('Content-Type: application/json');
    echo json_encode($cepData);
} catch (Exception $e) {
    http_response_code(404); // Código HTTP 404 para indicar que o recurso não foi encontrado
    echo json_encode(['error' => $e->getMessage()]);
}