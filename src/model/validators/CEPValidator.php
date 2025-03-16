<?php

namespace   model\validators;

use Exception;

class CEPValidator
{
    private string $cep;
    private string $apiUrl;

    public function __construct(string $cep, string $apiUrl = 'https://viacep.com.br/ws/')
    {
        $this->cep = $this->sanitizeCEP($cep);
        $this->apiUrl = rtrim($apiUrl, '/') . '/'; // Garante que a URL termine com uma barra
    }

    private function sanitizeCEP(string $cep): string
    {
        // Remove qualquer caractere que não seja número
        return preg_replace('/[^0-9]/', '', $cep);
    }

    public function validate(): bool
    {
        if (strlen($this->cep) !== 8) {
            throw new Exception("CEP inválido. O CEP deve conter exatamente 8 dígitos.");
        }

        return true;
    }

    public function getCEPData(string $returnType = 'json'): array|\SimpleXMLElement
    {
        $this->validate();

        $url = $this->apiUrl . $this->cep . '/' . $returnType . '/';

        // Tenta usar cURL primeiro
        if (function_exists('curl_version')) {
            $response = $this->fetchDataWithCurl($url);
        } else {
            // Fallback para file_get_contents se cURL não estiver disponível
            $response = $this->fetchDataWithFileGetContents($url);
        }

        // Verifica se o CEP não foi encontrado
        
        if ($returnType === 'json') {
            $data = json_decode($response, true);
            if (isset($data['erro']) && $data['erro'] == true) {
                throw new Exception("CEP válido, mas não cadastrado nos Correios.");
            }

            return $data;
        } elseif ($returnType === 'xml') {
            $data = simplexml_load_string($response);
            if (isset($data->erro) && (string)$data->erro === 'true') {
                throw new Exception("CEP válido, mas não cadastrado nos Correios.");
            }
            return $data;
        } else {
            throw new Exception("Tipo de retorno inválido. Use 'json' ou 'xml'.");
        }
    }

    private function fetchDataWithCurl(string $url): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception("Erro ao acessar a API do ViaCEP: " . curl_error($ch));
        }

        curl_close($ch);

        return $response;
    }

    private function fetchDataWithFileGetContents(string $url): string
    {
        // Verifica se allow_url_fopen está habilitado no php.ini
        if (!ini_get('allow_url_fopen')) {
            throw new Exception(
                "A função file_get_contents não pode ser usada porque allow_url_fopen está desabilitado no servidor."
            );
        }

        $response = file_get_contents($url);

        if ($response === false) {
            throw new Exception("Erro ao acessar a API do ViaCEP usando file_get_contents.");
        }

        return $response;
    }
}