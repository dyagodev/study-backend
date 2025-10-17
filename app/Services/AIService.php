<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected $apiKey;
    protected $model;
    protected $maxTokens;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->model = config('services.openai.model', 'gpt-4-turbo-preview');
        $this->maxTokens = (int) config('services.openai.max_tokens', 2000);
    }

    public function gerarQuestoesPorTema(string $tema, string $assunto, int $quantidade = 5, string $nivel = 'concurso')
    {
        $prompt = $this->construirPromptPorTema($tema, $assunto, $quantidade, $nivel);
        
        try {
            $response = $this->chamarOpenAI($prompt);
            return $this->processarRespostaQuestoes($response);
        } catch (Exception $e) {
            Log::error('Erro ao gerar questões por tema: ' . $e->getMessage());
            throw new Exception('Não foi possível gerar as questões. Por favor, tente novamente.');
        }
    }

    public function gerarVariacoesQuestao(string $questaoExemplo, int $quantidade = 3, string $nivel = 'medio')
    {
        $prompt = $this->construirPromptVariacao($questaoExemplo, $quantidade, $nivel);
        
        try {
            $response = $this->chamarOpenAI($prompt);
            return $this->processarRespostaQuestoes($response);
        } catch (Exception $e) {
            Log::error('Erro ao gerar variações de questão: ' . $e->getMessage());
            throw new Exception('Não foi possível gerar as variações. Por favor, tente novamente.');
        }
    }

    public function gerarQuestoesPorImagem(string $imagemBase64, string $contexto = '', string $nivel = 'medio')
    {
        $prompt = $this->construirPromptImagem($contexto, $nivel);
        
        try {
            $response = $this->chamarOpenAIComImagem($prompt, $imagemBase64);
            return $this->processarRespostaQuestoes($response);
        } catch (Exception $e) {
            Log::error('Erro ao gerar questões por imagem: ' . $e->getMessage());
            throw new Exception('Não foi possível analisar a imagem. Por favor, tente novamente.');
        }
    }

    protected function construirPromptPorTema(string $tema, string $assunto, int $quantidade, string $nivel): string
    {
        $nivelDescricao = match($nivel) {
            'facil' => 'FÁCIL',
            'medio' => 'MÉDIO',
            'dificil' => 'DIFÍCIL',
            'muito_dificil' => 'MUITO DIFÍCIL',
            default => 'MÉDIO',
        };

        return "Você é um especialista em educação e elaboração de questões para CONCURSOS PÚBLICOS BRASILEIROS.

Crie {$quantidade} questões de múltipla escolha do tipo CONCURSO PÚBLICO sobre o tema '{$tema}', especificamente sobre o assunto '{$assunto}'.

**NÍVEL DE DIFICULDADE EXIGIDO: {$nivelDescricao}**

Todas as questões devem ser no estilo de CONCURSO PÚBLICO BRASILEIRO, porém ajustadas ao nível de dificuldade {$nivelDescricao}:

- **FÁCIL**: Questões de concurso que abordam conceitos básicos e diretos, exigindo conhecimento fundamental sobre o assunto
- **MÉDIO**: Questões de concurso que exigem interpretação e aplicação de conceitos, com raciocínio moderado
- **DIFÍCIL**: Questões de concurso complexas que exigem análise crítica, correlação de múltiplos conceitos e raciocínio avançado
- **MUITO DIFÍCIL**: Questões de concurso de alta complexidade, exigindo conhecimento profundo, interpretação de casos complexos e raciocínio expert

Para cada questão, forneça:
1. Um enunciado claro e objetivo no estilo de concurso público
2. 4 alternativas de resposta
3. Indique qual alternativa é a correta
4. Uma breve explicação da resposta correta

IMPORTANTE: 
- TODAS as questões devem ser do tipo CONCURSO PÚBLICO
- O nível de dificuldade deve ser EXATAMENTE {$nivelDescricao}
- NÃO crie questões que dependam de imagens, gráficos, figuras ou diagramas
- Todas as questões devem ser compreensíveis apenas com texto
- Evite usar frases como \"Observe a figura\", \"Analise o gráfico\", \"De acordo com a imagem\", etc.
- Descreva todas as informações necessárias diretamente no enunciado
- Use linguagem formal e técnica apropriada para concursos públicos

Retorne no formato JSON:
[
  {
    \"enunciado\": \"texto da questão\",
    \"alternativas\": [
      {\"texto\": \"alternativa A\", \"correta\": false},
      {\"texto\": \"alternativa B\", \"correta\": true},
      {\"texto\": \"alternativa C\", \"correta\": false},
      {\"texto\": \"alternativa D\", \"correta\": false}
    ],
    \"explicacao\": \"explicação da resposta correta\"
  }
]";
    }

    protected function construirPromptVariacao(string $questaoExemplo, int $quantidade, string $nivel = 'medio'): string
    {
        $nivelDescricao = match($nivel) {
            'facil' => 'FÁCIL',
            'medio' => 'MÉDIO',
            'dificil' => 'DIFÍCIL',
            'muito_dificil' => 'MUITO DIFÍCIL',
            default => 'MÉDIO',
        };

        return "Você é um especialista em educação e elaboração de questões para CONCURSOS PÚBLICOS BRASILEIROS.

Com base na seguinte questão de exemplo, crie {$quantidade} questões similares no estilo CONCURSO PÚBLICO, mas com variações no conteúdo.

**NÍVEL DE DIFICULDADE EXIGIDO: {$nivelDescricao}**

Questão de exemplo:
{$questaoExemplo}

Para cada questão, forneça:
1. Um enunciado claro e objetivo no estilo de concurso público
2. 4 alternativas de resposta
3. Indique qual alternativa é a correta
4. Uma breve explicação da resposta correta

IMPORTANTE: 
- TODAS as questões devem ser do tipo CONCURSO PÚBLICO
- O nível de dificuldade deve ser EXATAMENTE {$nivelDescricao}
- Mantenha o formato e estilo da questão original
- NÃO crie questões que dependam de imagens, gráficos, figuras ou diagramas
- Todas as questões devem ser compreensíveis apenas com texto
- Descreva todas as informações necessárias diretamente no enunciado
- Use linguagem formal e técnica apropriada para concursos públicos

Retorne no formato JSON:
[
  {
    \"enunciado\": \"texto da questão\",
    \"alternativas\": [
      {\"texto\": \"alternativa A\", \"correta\": false},
      {\"texto\": \"alternativa B\", \"correta\": true},
      {\"texto\": \"alternativa C\", \"correta\": false},
      {\"texto\": \"alternativa D\", \"correta\": false}
    ],
    \"explicacao\": \"explicação da resposta correta\"
  }
]";
    }

    protected function construirPromptImagem(string $contexto, string $nivel = 'medio'): string
    {
        $contextoTexto = $contexto ? "Contexto adicional: {$contexto}\n\n" : '';
        
        $nivelDescricao = match($nivel) {
            'facil' => 'FÁCIL',
            'medio' => 'MÉDIO',
            'dificil' => 'DIFÍCIL',
            'muito_dificil' => 'MUITO DIFÍCIL',
            default => 'MÉDIO',
        };

        return "Você é um especialista em educação e elaboração de questões para CONCURSOS PÚBLICOS BRASILEIROS.

{$contextoTexto}Analise a imagem fornecida e crie 3 questões de múltipla escolha no estilo CONCURSO PÚBLICO baseadas no conteúdo visual.

**NÍVEL DE DIFICULDADE EXIGIDO: {$nivelDescricao}**

Para cada questão, forneça:
1. Um enunciado claro relacionado à imagem no estilo de concurso público - descreva textualmente o que está na imagem
2. 4 alternativas de resposta
3. Indique qual alternativa é a correta
4. Uma breve explicação da resposta correta

IMPORTANTE: 
- TODAS as questões devem ser do tipo CONCURSO PÚBLICO
- O nível de dificuldade deve ser EXATAMENTE {$nivelDescricao}
- Descreva completamente o conteúdo da imagem no enunciado, pois o usuário não terá acesso à imagem original
- Use linguagem formal e técnica apropriada para concursos públicos

Retorne no formato JSON:
[
  {
    \"enunciado\": \"texto da questão com descrição completa do conteúdo da imagem\",
    \"alternativas\": [
      {\"texto\": \"alternativa A\", \"correta\": false},
      {\"texto\": \"alternativa B\", \"correta\": true},
      {\"texto\": \"alternativa C\", \"correta\": false},
      {\"texto\": \"alternativa D\", \"correta\": false}
    ],
    \"explicacao\": \"explicação da resposta correta\"
  }
]";
    }

    protected function chamarOpenAI(string $prompt): string
    {
        // Aumentar tempo limite de execução para chamadas de IA (120 segundos)
        set_time_limit(120);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(90)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Você é um assistente especializado em criar questões educacionais. Sempre retorne respostas no formato JSON especificado.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => $this->maxTokens,
            'temperature' => 0.7,
        ]);

        if ($response->failed()) {
            throw new Exception('Erro na comunicação com a API da OpenAI: ' . $response->body());
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? '';
    }

    protected function chamarOpenAIComImagem(string $prompt, string $imagemBase64): string
    {
        // Aumentar tempo limite de execução para chamadas de IA com imagem (120 segundos)
        set_time_limit(120);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(90)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o',  // Updated to gpt-4o (supports vision natively)
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $prompt
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:image/jpeg;base64,{$imagemBase64}"
                            ]
                        ]
                    ]
                ]
            ],
            'max_tokens' => $this->maxTokens,
        ]);

        if ($response->failed()) {
            throw new Exception('Erro na comunicação com a API da OpenAI: ' . $response->body());
        }

        $data = $response->json();
        return $data['choices'][0]['message']['content'] ?? '';
    }

    protected function processarRespostaQuestoes(string $response): array
    {
        // Normalizar e remover blocos de código markdown
        $texto = preg_replace('/```json\s*(.*?)```/is', '$1', $response);
        $texto = preg_replace('/```\s*(.*?)```/is', '$1', $texto);
        $texto = trim($texto);

        // Tentar decodificar diretamente
        $questoes = json_decode($texto, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($questoes)) {
            return $questoes;
        }

        // Se JSON truncado, tentar extrair questões completas antes do truncamento
        // Procurar por array que começa com [ e tentar fechar com ]
        if (preg_match('/^\s*\[/s', $texto) && !preg_match('/\]\s*$/s', $texto)) {
            // JSON truncado - tentar adicionar ] para fechar
            $textoCorrigido = rtrim($texto, " \t\n\r\0\x0B,") . ']';
            $decoded = json_decode($textoCorrigido, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && count($decoded) > 0) {
                Log::warning('Resposta da IA foi truncada, mas ' . count($decoded) . ' questão(ões) válida(s) foram recuperadas.');
                return $decoded;
            }
            
            // Tentar remover última entrada incompleta e fechar array
            $ultimaVirgula = strrpos($texto, '},');
            if ($ultimaVirgula !== false) {
                $textoCorrigido = substr($texto, 0, $ultimaVirgula + 1) . ']';
                $decoded = json_decode($textoCorrigido, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && count($decoded) > 0) {
                    Log::warning('Resposta da IA foi truncada, última questão removida. ' . count($decoded) . ' questão(ões) válida(s) foram recuperadas.');
                    return $decoded;
                }
            }
        }

        // Procurar por um array JSON completo que começa com [
        if (preg_match('/(\[.*\])/sU', $texto, $matches)) {
            $candidate = $matches[1];
            $decoded = json_decode($candidate, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        // Procurar por um objeto JSON que começa com {
        if (preg_match('/(\{.*\})/sU', $texto, $matches)) {
            $candidate = $matches[1];
            $decoded = json_decode($candidate, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // Se for um objeto único, encapsular em array para manter contrato
                return is_array($decoded) ? (array) $decoded : [$decoded];
            }
        }

        // Se ainda assim falhar, logar a resposta bruta para análise e lançar exceção com contexto
        Log::error('Resposta bruta da IA com formato inesperado: ' . substr($texto, 0, 1000));
        throw new Exception('Erro ao processar resposta da IA: formato JSON inválido ou conteúdo inesperado. Resposta bruta registrada nos logs.');
    }
}
