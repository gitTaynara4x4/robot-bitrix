<?php
error_reporting(0);

#####################
### CONFIG OF BOT ###
#####################
define('DEBUG_FILE_NAME', ''); // Nome do arquivo de log (se necessário)
define('CLIENT_ID', 'local.6751b2766a4e46.20773958'); // ID do aplicativo Bitrix24
define('CLIENT_SECRET', 'kGd78loG14VQk4nO63Bulxx6KAMzGFLetibVhK0m4favTBfLqI'); // Chave do aplicativo Bitrix24
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

function restCommand($method, $params = array(), $auth = array(), $authRefresh = false)
{
    $queryUrl = $auth["client_endpoint"] . $method;
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

writeToLog($_REQUEST, 'ImBot Event Query');

$appsConfig = array();
if (file_exists(__DIR__ . '/config.php')) {
    include(__DIR__ . '/config.php');
}

// receive event "new message for bot"
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

// receive event "new command for bot"
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

// receive event "open private dialog with bot" or "join bot to group chat"
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
