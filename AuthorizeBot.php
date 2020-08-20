<?php

include_once("Client.php");

    class AuthorizeBot{
        private $clientID;
        private $redirectURI;
        private $token;

        public function __construct($clientID,$redirectURI,$token){
            $this->clientID = $clientID;
            $this->redirectURI = $redirectURI;
            $this->token = $token;
            $this->authorize();
        }
        public function authorize(){
            if (!isset($_GET["code"])){
                $params = array(
                    'client_id' => $this->clientID,
                    'redirect_uri' => $this->redirectURI,
                    'response_type' => 'code',
                    'scope' => 'bot',
                    'permissions'=>"8"
                );
                header('Location: https://discordapp.com/api/oauth2/authorize' . '?' . http_build_query($params,'flags_'));
            }
            //identify to gateway
            /*
            $client = new Client("wss://gateway.discord.gg:443");
            $client->receive() . "\n";
            $client->send('{
                "op": 2,
                "d": {
                "token": "'.$this->token.'",
                "properties": {
                    "$os": "windows",
                    "$browser": "chrome",
                    "$device": "ozan2"
                }
                }
            }');
            $client->receive() . "\n";
            */
        }

    }