# Diretrizes Técnicas do Gemini

Este arquivo contém as instruções e padrões aprovados para o desenvolvimento deste projeto.

## 📋 Padrões de Codificação
- **Backend:** Seguir PSR-12 para PHP.
- **Frontend:** Componentes Vue 3 utilizando `<script setup lang="ts">`.
- **Commits:** Mensagens em Inglês, claras e concisas.
- **Autorização:** Sempre utilizar `Policies` do Laravel para proteger recursos.
- **Controllers:** Manter controladores "magros" (Thin Controllers).

## 🛠️ Fluxo de Trabalho
1. **Alteração de Código:** Comitar apenas os arquivos alterados antes de qualquer deploy ou build pesado.
2. **Push:** Realizar `git push` após cada commit de funcionalidade.
3. **Validação:** Sempre utilizar a extensão `chrome-devtools` para validar alterações visuais ou de fluxo de usuário.
4. **Documentação:** Manter `TODO.md` atualizado com o progresso das tarefas.

## 🔍 Testes Automatizados
- Priorizar o uso do Chrome DevTools para simular interações de usuário reais.
- Documentar bugs encontrados no `TODO.md`.

## 💾 Memórias Úteis
- O usuário de teste padrão é `gemini@gemini` com a senha `gemini123`.
- O trait `AuthorizesRequests` deve ser usado na classe base `Controller.php`.
