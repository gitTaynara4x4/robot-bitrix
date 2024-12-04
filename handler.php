<?php

// Certifique-se de que os dados recebidos estão sendo processados corretamente
$inputData = file_get_contents('php://input');
$data = json_decode($inputData, true);

// Verificar se os dados são válidos
if ($data && isset($data['event'])) {
    // Aqui você pode fazer o que precisar com os dados
    // Exemplo: verificar qual evento foi recebido
    switch ($data['event']) {
        case 'ONIMBOTNEWCHAT':
            // Esse é um evento quando um novo chat é criado
            // Você pode processar o chat ou enviar uma resposta aqui
            break;
        case 'ONIMBOTSENDMESSAGE':
            // Esse evento ocorre quando uma mensagem é enviada para o chatbot
            // Aqui você pode fazer a automação necessária
            break;
        default:
            // Caso algum outro evento seja recebido
            break;
    }

    // Resposta para o Bitrix24 (opcional)
    echo json_encode(['status' => 'success']);
} else {
    // Se os dados não forem válidos, retorne um erro
    echo json_encode(['status' => 'error', 'message' => 'Dados inválidos']);
}
?>
