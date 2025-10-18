<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ValidaPayService
{
    protected $authUrl;
    protected $apiUrl;
    protected $clientId;
    protected $clientSecret;
    protected $tokenCacheKey = 'validapay_token';

    public function __construct()
    {
        $this->authUrl = config('services.validapay.auth_url', 'https://auth.validapay.com.br');
        $this->apiUrl = config('services.validapay.api_url', 'https://api.validapay.com.br');
        $this->clientId = config('services.validapay.client_id');
        $this->clientSecret = config('services.validapay.client_secret');
    }

    /**
     * Obter token de acesso (com cache)
     */
    protected function getAccessToken(): string
    {
        // Verificar se tem token em cache
        $cachedToken = Cache::get($this->tokenCacheKey);
        if ($cachedToken) {
            return $cachedToken;
        }

        // Gerar novo token
        try {
            Log::info('ValidaPay: Solicitando novo token', [
                'auth_url' => $this->authUrl,
                'client_id' => $this->clientId,
            ]);

            $response = Http::asForm()->post("{$this->authUrl}/oauth2/token", [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => 'pix.cob/write',
            ]);

            if ($response->failed()) {
                Log::error('ValidaPay: Erro na autenticação', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception('Erro ao autenticar com ValidaPay: ' . $response->body());
            }

            $data = $response->json();

            // Cachear token (expires_in - 60 segundos de margem)
            $expiresIn = $data['expires_in'] - 60;
            Cache::put($this->tokenCacheKey, $data['access_token'], $expiresIn);

            Log::info('ValidaPay: Token gerado com sucesso', [
                'expires_in' => $data['expires_in'],
            ]);

            return $data['access_token'];
        } catch (Exception $e) {
            Log::error('Erro ao obter token ValidaPay: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Criar cobrança PIX
     *
     * @param float $valor Valor em reais
     * @param string $webhookUrl URL do webhook para notificação
     * @param array $split Array de split de pagamento (opcional)
     * @return array ['transactionId' => int, 'emv' => string]
     */
    public function criarCobranca(
        float $valor,
        ?string $webhookUrl = null,
        array $split = []
    ): array {
        try {
            $token = $this->getAccessToken();
            $accountNumber = config('services.validapay.account_number');

            // Webhook padrão se não fornecido
            if (!$webhookUrl) {
                $webhookUrl = config('app.url') . '/api/webhook/validapay';
            }

            $body = [
                'amount' => (float) $valor,
                'webhook_url' => $webhookUrl,
                'split' => $split,
            ];

            Log::info('ValidaPay: Criando cobrança PIX', [
                'url' => "{$this->apiUrl}/pix?eventType=cob_pix",
                'body' => $body,
                'account_number' => $accountNumber,
            ]);

            $response = Http::withToken($token)
                ->withHeaders([
                    'X-Account-Number' => $accountNumber,
                    'Content-Type' => 'application/json',
                ])
                ->timeout(30)
                ->post("{$this->apiUrl}/pix?eventType=cob_pix", $body);

            if ($response->failed()) {
                $errorBody = $response->body();
                $errorJson = $response->json();

                Log::error('Erro ao criar cobrança ValidaPay', [
                    'status' => $response->status(),
                    'body' => $errorBody,
                    'json' => $errorJson,
                    'request' => $body,
                    'headers' => [
                        'X-Account-Number' => $accountNumber,
                        'Authorization' => 'Bearer ' . substr($token, 0, 20) . '...',
                    ],
                    'url' => "{$this->apiUrl}/pix?eventType=cob_pix",
                ]);

                $errorMessage = $errorJson['message'] ?? $errorJson['error'] ?? $errorBody;
                throw new Exception('Erro ao criar cobrança PIX: ' . $errorMessage);
            }

            $data = $response->json();

            Log::info('Cobrança PIX criada com sucesso', [
                'transactionId' => $data['transactionId'] ?? null,
                'valor' => $valor,
            ]);

            return $data;
        } catch (Exception $e) {
            Log::error('Erro ao criar cobrança PIX: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Consultar cobrança PIX por transactionId
     *
     * @param int $transactionId
     * @return array
     */
    public function consultarCobranca(int $transactionId): array
    {
        try {
            $token = $this->getAccessToken();
            $accountNumber = config('services.validapay.account_number');

            $response = Http::withToken($token)
                ->withHeaders([
                    'X-Account-Number' => $accountNumber,
                ])
                ->timeout(30)
                ->get("{$this->apiUrl}/pix/{$transactionId}");

            if ($response->failed()) {
                throw new Exception('Erro ao consultar cobrança: ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Erro ao consultar cobrança PIX: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Listar cobranças com filtros
     *
     * @param array $filtros ['inicio' => '2025-01-01T00:00:00Z', 'fim' => '2025-01-31T23:59:59Z', 'status' => 'ATIVA']
     * @return array
     */
    public function listarCobrancas(array $filtros = []): array
    {
        try {
            $token = $this->getAccessToken();

            $queryParams = array_merge([
                'inicio' => now()->subDays(7)->toIso8601String(),
                'fim' => now()->toIso8601String(),
            ], $filtros);

            $response = Http::withToken($token)
                ->timeout(30)
                ->get("{$this->apiUrl}/v2/cob", $queryParams);

            if ($response->failed()) {
                throw new Exception('Erro ao listar cobranças: ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Erro ao listar cobranças PIX: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Revisar cobrança (alterar valor, vencimento, etc)
     *
     * @param string $txid
     * @param array $dados
     * @return array
     */
    public function revisarCobranca(string $txid, array $dados): array
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withToken($token)
                ->timeout(30)
                ->patch("{$this->apiUrl}/v2/cob/{$txid}", $dados);

            if ($response->failed()) {
                throw new Exception('Erro ao revisar cobrança: ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Erro ao revisar cobrança PIX: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * O QR Code já vem no campo 'emv' da criação da cobrança
     * Não é necessário método separado para gerar QR Code
     */

    /**
     * Processar webhook de pagamento
     *
     * @param array $payload
     * @return array
     */
    public function processarWebhook(array $payload): array
    {
        try {
            Log::info('Webhook ValidaPay recebido', ['payload' => $payload]);

            // Extrair dados do webhook
            $transactionId = $payload['transactionId'] ?? $payload['transaction_id'] ?? null;
            $status = $payload['status'] ?? $payload['eventType'] ?? null;

            if (!$transactionId) {
                throw new Exception('Webhook sem transactionId');
            }

            // Retornar dados do webhook para processamento
            // A busca no banco será feita no controller
            return [
                'transactionId' => $transactionId,
                'status' => $status,
                'valor' => $payload['amount'] ?? null,
                'pagador' => $payload['payer'] ?? null,
                'horario' => $payload['createdAt'] ?? $payload['created_at'] ?? now()->toIso8601String(),
                'dados_completos' => $payload,
            ];
        } catch (Exception $e) {
            Log::error('Erro ao processar webhook ValidaPay: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Limpar CPF/CNPJ (remover pontos, traços, etc)
     *
     * @param string $documento
     * @return string
     */
    protected function limparCpfCnpj(string $documento): string
    {
        return preg_replace('/[^0-9]/', '', $documento);
    }

    /**
     * Validar status de pagamento
     *
     * @param string $status
     * @return bool
     */
    public function isPago(string $status): bool
    {
        // Status possíveis da ValidaPay: PENDING, CONFIRMED, PAID, CANCELLED, EXPIRED
        return in_array(strtoupper($status), ['CONFIRMED', 'PAID', 'CONCLUIDA', 'PAGO', 'COMPLETED']);
    }

    /**
     * Limpar cache do token (útil para testes)
     */
    public function limparCacheToken(): void
    {
        Cache::forget($this->tokenCacheKey);
    }
}
