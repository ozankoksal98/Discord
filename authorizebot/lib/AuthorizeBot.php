<?php

include_once("Client.php");

//current permission asks for administrator privileges , can be reduced later.
$clientID = "";
//redirect uri needs to be declared in the discord application page
$redirectURI = "";
//new bots token
$token = "";
//oauth request
if (!isset($_GET["code"])){
    $params = array(
        'client_id' => $clientID,
        'redirect_uri' => $redirectURI,
        'response_type' => 'code',
        'scope' => 'bot',
        'permissions'=>"8"
    );
    header('Location: https://discordapp.com/api/oauth2/authorize' . '?' . http_build_query($params,'flags_'));
}
//identify to gateway
$client = new Client("wss://gateway.discord.gg:443");
echo $client->receive() . "\n";
$client->send('{
    "op": 2,
    "d": {
    "token": "'.$token.'",
    "properties": {
        "$os": "windows",
        "$browser": "chrome",
        "$device": "ozan2"
    }
    }
}');
echo $client->receive() . "\n";