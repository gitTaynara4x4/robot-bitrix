# 🤖 Integração entre Bitrix24 ImBot e Dialog360 🇧🇷  
_(Scroll down for English version 🇺🇸)_

Este projeto PHP permite **registrar e gerenciar um bot (ImBot) no Bitrix24**, com integração ao Dialog360 para **responder automaticamente mensagens recebidas via WhatsApp**.

---

### ✅ O que ele faz?

- Recebe mensagens via webhook do Dialog360.
- Encaminha essas mensagens para o chat Bitrix24 via `imbot.message.add`.
- Registra comandos e respostas automáticas no bot.
- Gerencia o token de autenticação do Bitrix24.
- Possui log de eventos em arquivo para fácil rastreamento.

---

### 🔧 Como funciona?

1. Recebe eventos de mensagem ou comando via `$_REQUEST`.
2. Verifica o tipo de evento (`ONIMBOTMESSAGEADD`, `ONIMCOMMANDADD`, etc).
3. Usa a API do Bitrix24 para responder no mesmo diálogo.
4. Atualiza o token automaticamente se ele expirar.
5. Registra logs em `bot_debug.log`.

---

### ⚙️ Requisitos

- PHP 7+
- Acesso a uma conta Bitrix24 com permissões para ImBot
- Um servidor com suporte a HTTPS para receber webhooks do Dialog360

---

### 📈 Benefícios

- Automatização no atendimento via WhatsApp.
- Integração nativa com chats e OpenLine do Bitrix24.
- Controle total sobre mensagens, comandos e eventos.

> Precisa de ajuda para configurar seu bot no Bitrix24? Fale com a gente e automatize seu atendimento. 😉

---

# 🤖 Bitrix24 ImBot Integration with Dialog360 🇺🇸

This PHP project allows you to **register and manage a bot (ImBot) in Bitrix24**, integrated with Dialog360 to **automatically respond to WhatsApp messages**.

---

### ✅ What does it do?

- Receives webhook messages from Dialog360.
- Sends them to Bitrix24 chat using `imbot.message.add`.
- Handles commands like `/help`.
- Automatically refreshes expired access tokens.
- Logs all activity to `bot_debug.log`.

---

### 🔧 How it works

1. Listens to incoming events via `$_REQUEST`.
2. Identifies event type (e.g., `ONIMBOTMESSAGEADD`, `ONIMCOMMANDADD`).
3. Sends replies to Bitrix24 using the ImBot API.
4. Handles token updates transparently.
5. Logs everything for debugging.

---

### ⚙️ Requirements

- PHP 7+
- Bitrix24 account with ImBot API access
- HTTPS-enabled server to receive Dialog360 webhooks

---

### 📈 Business Benefits

- Automate WhatsApp customer service.
- Native integration with Bitrix24 chats and OpenLines.
- Full control of bot behavior and conversation flow.

> Want to automate your Bitrix24 with a custom chatbot? Let’s build it together. 🚀
