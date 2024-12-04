<?php

// Defina os parâmetros do bot
$bot_code = 'jjfs0ezqyec0uhts';
$client_id = 'y6yf13au38z1rcmeznlcd891lo6k22s2';
$bot_id = '152072';
$dialog_id = 'chat1';

// Exemplo de entrada recebida do Bitrix24
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Verifique se a requisição é válida e pertence ao bot correto
if (isset($data['bot_code']) && $data['bot_code'] === $bot_code) {

    // Defina o título do novo "deal" (negócio)
    $deal_title = 'Olá! Eu sou um chatbot!';
    
    // Montando a URL para adicionar o novo "deal" no Bitrix24
    $url = 'https://marketingsolucoes.bitrix24.com.br/rest/5332/37l1h62n1m3nif2e/crm.deal.add.json';
    $url .= '?BOT_ID=' . $bot_id;
    $url .= '&CLIENT_ID=' . $client_id;
    $url .= '&DIALOG_ID=' . $dialog_id;
    $url .= '&FIELDS[TITLE]=' . urlencode($deal_title);
    
    // Fazer a requisição para adicionar o "deal" no CRM
    $response = file_get_contents($url);
    
    // Lidar com a resposta do Bitrix24
    $response_data = json_decode($response, true);
    
    if (isset($response_data['result'])) {
        // Se o "deal" foi adicionado com sucesso, responda ao usuário
        $bot_response = 'O negócio foi criado com sucesso!';
    } else {
        // Caso contrário, notifique sobre erro
        $bot_response = 'Ocorreu um erro ao criar o negócio.';
    }

    // Retorne a resposta ao usuário do chatbot
    header('Content-Type: application/json');
    echo json_encode(['response' => $bot_response]);

} else {
    // Se o código do bot não for válido, rejeite a requisição
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Acesso não autorizado']);
}
?>
