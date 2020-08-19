<?php
    
    include "requests.php";
    require('vendor/autoload.php');
    use WebSocket\Client;

    class DiscordClient{
        private $baseUrl = "https://discord.com/api";
        private $clientID ;
        private $clientSecret ;
        private $code;
        private $token;
        private $refreshToken;
        private $redirectURL = "http://localhost/curl/DiscordClient.php";
        private $responseType = "code";
        private $authUrlBase = "https://discord.com/api/oauth2/authorize";
        private $authHeader ;
        private $guildID;
        private $gateway;
    
        public function __construct($clientID =null , $clientSecret=null ,$scopes,$bot = false){
            if (isset($_GET["code"])){
                if(isset($_GET["guild_id"])){
                    $this->clientID = $clientID;
                    $this->token = "NzQ0OTkwNTUyMTUxNDI1MTI0.XzrQhA.3XhMgKTcuYFYUNizHdAgjBfPD_w";
                    $this->guildID = $_GET["guild_id"];
                    $this->authHeader = 'Authorization: Bot ' . $this->token;
                    $this->gateway = json_decode($this->getGateway(),true)["url"];

                }else{
                    //get auth token and set instance variables
                $this->clientID = $clientID;    
                $this->clientSecret = $clientSecret;
                $this->code = $_GET["code"];
                $params = array(
                    "grant_type" => "authorization_code",
                    "client_id" => $this->clientID,
                    "client_secret" => $this->clientSecret,
                    "redirect_uri" => $this->redirectURL,
                    "code" => $this->code,
                );
                $headers = array(
                    "Content-Type"=> "application/x-www-form-urlencoded"
                );
                $result = Requests::postRequest($this->baseUrl."/oauth2/token",$params,$headers);
                if ($result == false) {
                    echo "error";
                    
                } else {
                    $error = null;
                    $result = json_decode($result,true);
                    print_r($result);
                }
                $this->token = $result["access_token"];
                $this->refreshToken = $result["refresh_token"];
                $this->authHeader ='Authorization: Bearer ' . $this->token;
                }
                
                
            }else{
                if($bot){
                    $params = array(
                        'client_id' => $clientID,
                        'redirect_uri' => $this->redirectURL,
                        'response_type' => 'code',
                        'scope' => 'bot',
                        'permissions'=>"8"
                      );
                    header('Location: https://discordapp.com/api/oauth2/authorize' . '?' . http_build_query($params,'flags_'));
                }else{
                    //get authorization code
                $params = array(
                    'client_id' => $clientID,
                    'redirect_uri' => $this->redirectURL,
                    'response_type' => 'code',
                    'scope' => 'connections identify guilds email messages.read '
                  );
                header('Location: https://discordapp.com/api/oauth2/authorize' . '?' . http_build_query($params,'flags_'));
                }
                
            }
        }

        public function getauthHeader(){
            //get authentication header
            return $this->authHeader;
        }

        public function getGateway(){
            //get gateway api 
            return Requests::getRequest($this->baseUrl."/gateway",array($this->authHeader));
        }

        public function getGatewayBot(){
            //get gateway bot info
            return Requests::getRequest($this->baseUrl."/gateway/bot",array($this->authHeader));
        }

        public function getUser(){
            //Gets info about user object
            return Requests::getRequest($this->baseUrl."/users/@me",array($this->authHeader));
        }

        public function getCurrentUserGuilds(){
            //Gets info about servers that the current user is in
            return Requests::getRequest($this->baseUrl."/users/@me/guilds",array($this->authHeader));
        }

        public function getUserConnections(){
            //get user connections spotify etc.
            return Requests::getRequest($this->baseUrl."/users/@me/connections",array($this->authHeader));
        }

        public function getGuildMembers(){
            return Requests::getRequest($this->baseUrl."/guilds/".$this->guildID."/members",array($this->authHeader));
        }

        public function getGuildChannels(){
            return Requests::getRequest($this->baseUrl."/guilds/".$this->guildID."/channels",array($this->authHeader));
        }

        public function sendTestMessage($channelID,$content){
            //Send test message
            echo Requests::postRequest($this->baseUrl."/channels/".$channelID."/messages", json_decode('{
                "content": "Hello, World!",
                "tts": "true"
              }',true),array("Content-Type: multipart/form-data",$this->getauthHeader()));
        }

        public function sendMessage($channelID,$content){
           //Send custom message
            echo Requests::postRequest($this->baseUrl."/channels/".$channelID."/messages", array("content"=>$content,"tts"=> "true"),
                array("Content-Type: multipart/form-data",$this->getauthHeader()));
        }
        
/*
        public function createWebHook($channelID){
            echo  Requests::postRequest($this->baseUrl."/channels/".$channelID."/webhooks",json_decode('{"name": "asdfasdf","avatar": "null"}',true),array("Content-Type: 'multipart/form-data'","Authorization: Bot NzQ0OTkwNTUyMTUxNDI1MTI0.XzrQhA.3XhMgKTcuYFYUNizHdAgjBfPD_w"));

        }
*/
        public function parseResponse($json_string){
            return json_decode($json_string,true);
        }
        
    }

    // "genel" channel id = 744853637385420924
    // guild id = 744853637385420921
    $api = new DiscordClient("744990552151425124","IvjLNMQxx4dXjcbUHFyvn7K4QOezxdr4","scopes to be filled later",true);
    echo $api->getGuildChannels();
    
      

    /*
    //WebSocket Identification
    //To initialize the bot to be able to send messages
    $client = new Client("wss://gateway.discord.gg:443");
    echo $client->receive() . "\n";
    $client->send('{
        "op": 2,
        "d": {
          "token": "NzQ0OTkwNTUyMTUxNDI1MTI0.XzrQhA.3XhMgKTcuYFYUNizHdAgjBfPD_w",
          "properties": {
            "\$os": "windows",
            "\$browser": "chrome",
            "\$device": "ozan"
          }
          }
      }');
      sleep(2);
    echo $client->receive() . "\n";
    echo"<br>";
    sleep(2);
    $client->send('{
        "op": 1,
        "d": 5
    }') . "\n";

    echo $client->receive() . "\n";
    echo"<br>";
    sleep(2);
    $client->send('{
        "op": 1,
        "d": null
    }') . "\n";

    echo $client->receive() . "\n";
    echo"<br>";
    sleep(2);

    echo $client->isConnected();

    $client->close();
    */

    // "genel" channel id = 744853637385420924
    // guild id = 744853637385420921