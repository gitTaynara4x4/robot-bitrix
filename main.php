<?php

// Log de todas as requisições para depuração
file_put_contents("log.txt", date('Y-m-d H:i:s') . " - Entrada: " . json_encode($_REQUEST) . "\n", FILE_APPEND);

// Verifica se os dados esperados foram enviados pelo Bitrix24
if (isset($_REQUEST['BOT_ID']) && isset($_REQUEST['CLIENT_ID']) && isset($_REQUEST['DIALOG_ID']) && isset($_REQUEST['FIELDS']['TITLE'])) {
    $botId = $_REQUEST['BOT_ID'];
    $clientId = $_REQUEST['CLIENT_ID'];
    $dialogId = $_REQUEST['DIALOG_ID'];
    $title = $_REQUEST['FIELDS']['TITLE'];

    // Aqui você pode adicionar mais validações se necessário, como verificar o botId ou clientId

    // Criação de um "deal" no Bitrix24 via API
    $url = "https://marketingsolucoes.bitrix24.com.br/rest/5332/37l1h62n1m3nif2e/crm.deal.add.json";
    $fields = [
        "BOT_ID" => $botId,
        "CLIENT_ID" => $clientId,
        "DIALOG_ID" => $dialogId,
        "FIELDS" => [
            "TITLE" => $title
        ]
    ];

    // Inicializa a requisição para criar o "deal" no CRM do Bitrix24
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));

    // Recebe a resposta da API do Bitrix24
    $response = curl_exec($ch);
    curl_close($ch);

    // Log da resposta do servidor Bitrix24 para depuração
    file_put_contents("log.txt", date('Y-m-d H:i:s') . " - Resposta: " . $response . "\n", FILE_APPEND);

    // Envia a resposta de volta para o Bitrix24
    if ($response) {
        echo json_encode(["status" => "success", "message" => "Deal criado com sucesso!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Falha ao criar o deal."]);
    }
} else {
    // Caso algum dado esperado não tenha sido recebido
    file_put_contents("log.txt", date('Y-m-d H:i:s') . " - Erro: Dados incompletos ou inválidos.\n", FILE_APPEND);
    echo json_encode(["status" => "error", "message" => "Dados incompletos ou inválidos."]);
}

?>
