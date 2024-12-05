<?php
error_reporting(0);

// Definições de configuração do bot
define('DEBUG_FILE_NAME', 'bot_debug.log'); // Nome do arquivo de log
define('CLIENT_ID', 'local.6751b2766a4e46.20773958'); // ID do aplicativo Bitrix24
define('CLIENT_SECRET', 'kGd78loG14VQk4nO63Bulxx6KAMzGFLetibVhK0m4favTBfLqI'); // Chave do aplicativo Bitrix24
define('WEBHOOK_URL', 'https://falasolucoes-robo.ywsa8i.easypanel.host'); // URL do seu evento no Dialog360

// Função para registrar os dados no log
function writeToLog($data, $title = '')
{
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= (strlen($title) > 0 ? $title . "\n" : '');
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(__DIR__ . '/' . DEBUG_FILE_NAME, $log, FILE_APPEND);
    return true;
}

// Função para fazer a chamada à API do Bitrix24
function restCommand($method, $params = array(), $auth = array())
{
    if (!isset($auth["access_token"])) {
        return false;
    }

    // Atualiza o token caso esteja expirado
    if (isset($auth['expires_in']) && time() > $auth['expires_in']) {
        $auth = refreshAccessToken($auth);
    }

    $queryUrl = $auth["client_endpoint"] . $method;
    $queryData = http_build_query(array_merge($params, array("auth" => $auth["access_token"])));

    // Inicializa o CURL para enviar dados à API do Bitrix24
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $queryUrl,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $queryData,
    ));
    $result = curl_exec($curl);
    curl_close($curl);

    return json_decode($result, true);
}

// Função para atualizar o token de acesso
function refreshAccessToken($auth)
{
    $queryUrl = "https://oauth.bitrix.info/oauth/token/";
    $queryData = http_build_query(array(
        "grant_type" => "refresh_token",
        "client_id" => CLIENT_ID,
        "client_secret" => CLIENT_SECRET,
        "refresh_token" => $auth["refresh_token"],
    ));

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_URL => $queryUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $queryData,
    ));
    $result = curl_exec($curl);
    curl_close($curl);

    $newAuth = json_decode($result, true);
    if (isset($newAuth['access_token'])) {
        writeToLog($newAuth, 'Token Atualizado');
        return $newAuth;
    } else {
        writeToLog($newAuth, 'Erro ao Atualizar Token');
        return $auth;
    }
}

// Função para registrar o bot
function registerBot()
{
    global $appsConfig;

    $result = restCommand('imbot.register', array(
        'CODE' => 'MeuBot',
        'TYPE' => 'B',
        'EVENT_MESSAGE_ADD' => WEBHOOK_URL,
        'EVENT_WELCOME_MESSAGE' => WEBHOOK_URL,
        'OPENLINE' => 'Y',
    ), $appsConfig[CLIENT_ID]);

    writeToLog($result, 'Registro do Bot');
}

writeToLog($_REQUEST, 'ImBot Event Query');

// Recebe a mensagem do webhook do Dialog360
if ($_REQUEST['event'] == 'ONIMBOTMESSAGEADD') {
    $message = $_REQUEST['data']['PARAMS']['MESSAGE'];  // Mensagem recebida
    $dialogId = $_REQUEST['data']['PARAMS']['DIALOG_ID']; // ID do diálogo

    // Exemplo de enviar a mensagem para o Bitrix24
    $bitrixAuth = ['access_token' => 'SEU_TOKEN_DE_AUTORIZAÇÃO_DO_BITRIX']; // Substitua pelo token correto

    $result = restCommand('imbot.message.add', array(
        "DIALOG_ID" => $dialogId, // ID do bate-papo
        "MESSAGE" => $message, // Mensagem
    ), $bitrixAuth);

    writeToLog($result, 'Mensagem Recebida do WhatsApp');
}

// Lida com comandos recebidos no bot
if ($_REQUEST['event'] == 'ONIMCOMMANDADD') {
    if (!isset($appsConfig[$_REQUEST['auth']['application_token']])) {
        return false;
    }

    $latency = (time() - $_REQUEST['ts']);
    $latency = $latency > 60 ? (round($latency / 60)) . 'm' : $latency . "s";

    $result = false;
    foreach ($_REQUEST['data']['COMMAND'] as $command) {
        if ($command['COMMAND'] == 'help') {
            $result = restCommand('imbot.command.answer', array(
                "COMMAND_ID" => $command['COMMAND_ID'],
                "MESSAGE_ID" => $command['MESSAGE_ID'],
                "MESSAGE" => "Olá! Este é o bot de ajuda.",
            ), $_REQUEST["auth"]);
        }
    }

    writeToLog($result, 'ImBot Command Add');
}

// Recebe evento de "entrar em chat privado com o bot" ou "adicionar o bot ao grupo"
if ($_REQUEST['event'] == 'ONIMBOTJOINCHAT') {
    if (!isset($appsConfig[$_REQUEST['auth']['application_token']])) {
        return false;
    }

    $result = restCommand('imbot.message.add', array(
        "DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
        "MESSAGE" => "Bem-vindo! Como posso ajudar?",
    ), $_REQUEST["auth"]);

    writeToLog($result, 'ImBot Join Chat');
}

?>
