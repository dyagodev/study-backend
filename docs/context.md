🧩 Visão Geral da Plataforma
🎯 Objetivo

Permitir que o usuário:

Escolha um tema (ex: Biologia, História, Matemática);

Gere automaticamente questões sobre esse tema (usando IA);

Cole um exemplo de questão para gerar outras semelhantes;

Envie uma imagem (ex: gráfico, mapa, diagrama) e receba perguntas baseadas nela.

🧠 Módulos Principais
1. Autenticação e Perfis

Login/cadastro (email/senha, Google, etc)

Perfis de aluno e professor (ou modo livre)

Painel do usuário com histórico de questões geradas

2. Geração de Questões

Opção 1: Usuário escolhe tema → IA gera perguntas e respostas

Opção 2: Usuário cola uma questão → IA cria variações

Opção 3: Usuário envia imagem → IA analisa e gera perguntas (via OCR e visão computacional)

⚙️ Essa parte pode usar uma API de IA (como OpenAI ou modelo local) para gerar perguntas no formato:

{
  "questao": "Qual é a principal função das mitocôndrias?",
  "alternativas": ["Síntese proteica", "Respiração celular", "Digestão", "Transporte de substâncias"],
  "resposta_correta": "Respiração celular",
  "explicacao": "As mitocôndrias produzem energia por meio da respiração celular."
}

3. Banco de Questões

Salvar questões geradas e permitir organizar por tema, nível e tags

Filtrar, revisar, exportar (PDF ou Quiz online)

Professores podem criar coleções ou simulados

4. Interface de Estudo

Modo Quiz (com pontuação)

Revisão de erros

Explicação automática após resposta

Recomendação de novos temas com base em desempenho
