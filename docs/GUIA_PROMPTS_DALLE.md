# 🎨 Guia de Prompts Eficazes para DALL-E 3

## Problema: Imagens "Nada a Ver"

Quando o prompt é vago ou genérico, o DALL-E gera imagens artísticas ou fotorrealistas que não servem para fins educacionais.

### ❌ Prompts RUINS (Genéricos)

```
"Create a triangle"
→ Gera: Triângulo artístico, 3D, colorido, nada educacional

"Show a cell"
→ Gera: Foto realista de célula, sem labels, confusa

"Make a graph"
→ Gera: Gráfico corporativo estilizado, não matemático
```

### ✅ Prompts BONS (Específicos)

```
"A simple right triangle with right angle at bottom-left corner, 
angle of 30° at bottom-right labeled 'α', angle of 60° at top 
labeled 'β', hypotenuse labeled '10cm', height labeled 'h', 
clean black lines on white background, minimalist educational 
diagram style, suitable for mathematics textbook"

→ Gera: Diagrama educacional limpo e preciso
```

## Anatomia de um Bom Prompt

### 1. Tipo de Imagem
Sempre comece especificando o tipo:
- "A simple diagram of..."
- "A clean line drawing of..."
- "A minimalist illustration showing..."
- "An educational chart depicting..."

### 2. Elementos Específicos
Liste TODOS os elementos que devem aparecer:
- "showing a right triangle"
- "with labeled axes x and y"
- "including three labeled organelles"
- "with forces F1 and F2 indicated by arrows"

### 3. Medidas e Labels
Especifique valores e rótulos:
- "hypotenuse of 10cm labeled"
- "angle of 45° marked"
- "point A at (2,3)"
- "force of 50N indicated"

### 4. Estilo Visual
Defina claramente o estilo desejado:
- "clean black lines on white background"
- "minimalist style"
- "suitable for educational textbook"
- "simple colors with clear labels"
- "line drawing, not photorealistic"

## Templates por Área

### 📐 Geometria

```
Template:
"A simple [tipo] with [características], [medidas], 
[ângulos], [labels], clean black lines on white 
background, minimalist educational diagram"

Exemplo:
"A simple equilateral triangle with all sides measuring 
5cm labeled, all angles 60° marked, vertices labeled A, 
B, C, clean black lines on white background, minimalist 
educational diagram"
```

### 📊 Gráficos/Funções

```
Template:
"A clean line graph showing [função], [pontos importantes], 
with labeled x and y axes, [escala], black line on white 
background with grid"

Exemplo:
"A clean line graph showing a parabola y=x², passing through 
points (-2,4), (0,0), and (2,4), with labeled x and y axes, 
scale from -3 to 3 on x-axis, black line on white background 
with light gray grid"
```

### 🔬 Biologia

```
Template:
"A simple diagram of [estrutura], showing labeled [partes], 
[cores simples para diferenciar], educational textbook style"

Exemplo:
"A simple diagram of a plant cell, showing labeled nucleus 
(blue circle in center), chloroplasts (green ovals, 4-5 pieces), 
cell wall (outer black rectangle), vacuole (large light blue 
circle), mitochondria (3 small red ovals), simple educational 
textbook style with clear labels"
```

### ⚡ Física

```
Template:
"A simple diagram showing [cenário], with [forças/vetores] 
indicated by arrows, [medidas], black lines on white, 
minimalist physics textbook style"

Exemplo:
"A simple diagram showing a box on an inclined plane at 30°, 
with force vectors: weight (50N downward), normal force (perpendicular 
to plane), friction force (up the plane), all forces indicated by 
labeled arrows, black lines on white background, minimalist physics 
textbook style"
```

### 🧪 Química

```
Template:
"A simple molecular structure showing [molécula], with [átomos] 
labeled, [ligações], clean educational chemistry diagram"

Exemplo:
"A simple molecular structure showing water (H2O), with oxygen 
atom (red circle) labeled O in center, two hydrogen atoms (white 
circles) labeled H on sides, bond angle 104.5° indicated, clean 
educational chemistry diagram with black lines on white background"
```

## Palavras-Chave Importantes

### Para Estilo Educacional
- "simple"
- "clean"
- "minimalist"
- "educational"
- "textbook style"
- "diagram"
- "line drawing"
- "schematic"

### Para Evitar Estilo Artístico
- "not photorealistic"
- "not artistic"
- "not 3D"
- "not stylized"
- "black and white" ou "simple colors"

### Para Garantir Clareza
- "labeled"
- "with clear labels"
- "measurements indicated"
- "values shown"
- "arrows indicating"

## Checklist de Qualidade

Antes de enviar o prompt, verifique:

- [ ] Especificou "simple diagram" ou similar?
- [ ] Listou TODOS os elementos que devem aparecer?
- [ ] Incluiu medidas/valores específicos?
- [ ] Adicionou "labeled" para elementos importantes?
- [ ] Definiu "black lines on white background"?
- [ ] Incluiu "educational" ou "textbook style"?
- [ ] Evitou termos artísticos/fotorrealistas?
- [ ] Prompt tem mais de 50 palavras? (quanto mais específico, melhor)

## Exemplos Completos

### Exemplo 1: Trigonometria

**Questão:** "Observe o triângulo retângulo na figura. Se o cateto adjacente mede 8cm e a hipotenusa mede 10cm, qual é o valor do outro cateto?"

**Prompt Ruim:**
```
"A right triangle with sides"
```

**Prompt Bom:**
```
"A simple right triangle diagram with the right angle at the 
bottom-left corner. The horizontal side (adjacent cathetus) is 
labeled '8 cm', the hypotenuse (diagonal line from bottom-right 
to top-left) is labeled '10 cm', and the vertical side (opposite 
cathetus) is labeled 'x' with a question mark. All three angles 
are marked. The vertices are labeled A (bottom-left), B (bottom-right), 
and C (top-left). Clean black lines on white background, minimalist 
educational geometry diagram suitable for a mathematics textbook."
```

### Exemplo 2: Gráfico de Função

**Questão:** "Analise o gráfico da função f(x) = 2x + 1. Qual é o valor de f(3)?"

**Prompt Ruim:**
```
"Graph of a linear function"
```

**Prompt Bom:**
```
"A clean coordinate plane showing a linear function f(x) = 2x + 1. 
The line passes through points (0,1) and (3,7), with these points 
marked and labeled. The x-axis ranges from -2 to 4, y-axis from -2 
to 8. Both axes are labeled. The line is drawn in black. Light gray 
grid in background. The point (3,7) is highlighted with a red dot. 
Clean minimalist style suitable for a mathematics textbook."
```

### Exemplo 3: Célula

**Questão:** "Observe a célula vegetal na figura. Qual organela é responsável pela fotossíntese?"

**Prompt Ruim:**
```
"Plant cell diagram"
```

**Prompt Bom:**
```
"A simple educational diagram of a plant cell (rectangular shape). 
Shows and labels: nucleus (large blue circle in center), chloroplasts 
(5-6 green oval shapes distributed in cytoplasm), cell wall (thick 
outer black rectangle), cell membrane (thin line inside cell wall), 
large vacuole (light blue oval taking up 30% of cell), mitochondria 
(3-4 small orange ovals). Each organelle clearly labeled with text 
and arrows. Simple, clean educational textbook style with basic 
colors on white background."
```

## Dicas Finais

1. **Mais detalhes = Melhor resultado**
   - Prompts de 100-200 palavras funcionam melhor que 20 palavras

2. **Use números específicos**
   - "angle of 45°" > "an angle"
   - "5 chloroplasts" > "some chloroplasts"

3. **Sempre especifique "educational" ou "textbook"**
   - Isso muda completamente o estilo gerado

4. **Prefira "black lines on white background"**
   - Mais limpo e fácil de ler que cores complexas

5. **Liste elementos em ordem de importância**
   - DALL-E dá mais atenção ao início do prompt

## Testando e Iterando

Se a imagem gerada não ficou boa:

1. **Adicione mais especificidade** ao prompt
2. **Reforce o estilo** ("very simple", "extremely minimal")
3. **Liste elementos um por um** com posições exatas
4. **Evite ambiguidade** (se disser "triângulo", especifique que tipo)

---

**Lembre-se:** O DALL-E é LITERAL. Se você não especificar, ele vai criar algo artístico. Quanto mais você especificar, melhor o resultado educacional!
