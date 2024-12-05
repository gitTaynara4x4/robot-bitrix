<?php
error_reporting(0);

#####################
### CONFIG OF BOT ###
#####################
define('DEBUG_FILE_NAME', 'bot_debug.log'); // Nome do arquivo de log
define('CLIENT_ID', 'local.6751b2766a4e46.20773958'); // ID do aplicativo Bitrix24
define('CLIENT_SECRET', 'kGd78loG14VQk4nO63Bulxx6KAMzGFLetibVhK0m4favTBfLqI'); // Chave do aplicativo Bitrix24
define('WEBHOOK_URL', 'https://falasolucoes-robo.ywsa8i.easypanel.host'); // URL do seu evento
define('BITRIX24_URL', 'https://marketingsolucoes.bitrix24.com.br/rest/35002/7a2nuej815yjx5bg/'); // URL do seu webhook do Bitrix24

#####################

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

function restCommand($method, $params = array(), $auth = array())
{
    if (!isset($auth["access_token"])) {
        return false;
    }

    // Atualiza o token caso esteja expirado
    if (isset($auth['expires_in']) && time() > $auth['expires_in']) {
        $auth = refreshAccessToken($auth);
    }

    $queryUrl = BITRIX24_URL . $method;
    $queryData = http_build_query(array_merge($params, array("auth" => $auth["access_token"])));

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

// Aqui começa a parte de receber as mensagens do WhatsApp e enviar para o Bitrix24
$data = json_decode(file_get_contents("php://input"), true);

// Verificar se há mensagens
if (isset($data['messages']) && is_array($data['messages'])) {
    foreach ($data['messages'] as $message) {
        $from = $message['from']; // Número do cliente
        $body = $message['text']['body']; // Texto da mensagem

        // Enviar para o Bitrix24
        $bitrixAuth = ['access_token' => 'SEU_TOKEN_DE_AUTORIZAÇÃO_DO_BITRIX']; // Defina seu token de autorização

        $result = restCommand('imbot.message.add', array(
            "DIALOG_ID" => $from, // ID do bate-papo (pode ser o número do cliente)
            "MESSAGE" => $body,
        ), $bitrixAuth);

        // Registrar os dados no log
        writeToLog($data, 'Mensagem Recebida do WhatsApp');
    }
}

writeToLog($_REQUEST, 'ImBot Event Query');

$appsConfig = array();
if (file_exists(__DIR__ . '/config.php')) {
    include(__DIR__ . '/config.php');
}

// Registra o bot ao inicializar (caso ainda não esteja registrado)
if ($_REQUEST['event'] == 'ONAPPINSTALL') {
    registerBot();
}

// Recebe o evento "nova mensagem para o bot"
if ($_REQUEST['event'] == 'ONIMBOTMESSAGEADD') {
    if (!isset($appsConfig[$_REQUEST['auth']['application_token']])) {
        return false;
    }

    $latency = (time() - $_REQUEST['ts']);
    $latency = $latency > 60 ? (round($latency / 60)) . 'm' : $latency . "s";

    if ($_REQUEST['data']['PARAMS']['CHAT_ENTITY_TYPE'] == 'LINES') {
        list($message) = explode(" ", $_REQUEST['data']['PARAMS']['MESSAGE']);
        if ($message == '1') {
            $result = restCommand('imbot.message.add', array(
                "DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
                "MESSAGE" => 'Olá, sou o EchoBot! Eu posso repetir mensagens e enviar menus nos canais abertos!',
            ), $_REQUEST["auth"]);
        } elseif ($message == '0') {
            $result = restCommand('imbot.message.add', array(
                "DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
                "MESSAGE" => 'Aguarde uma resposta!',
            ), $_REQUEST["auth"]);
        }
    } else {
        $result = restCommand('imbot.message.add', array(
            "DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
            "MESSAGE" => "Mensagem do bot",
            "ATTACH" => array(
                array("MESSAGE" => "Resposta: " . $_REQUEST['data']['PARAMS']['MESSAGE']),
                array("MESSAGE" => "Latência: " . $latency)
            )
        ), $_REQUEST["auth"]);
    }

    writeToLog($result, 'ImBot Event Message Add');
}

// Recebe o evento "novo comando para o bot"
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

// Recebe o evento "abrir diálogo privado com o bot" ou "adicionar bot ao grupo"
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
