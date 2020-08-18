<?php
    
    include "requests.php";
    require "./websocket_client.php";
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
                    $this->token = "NzQ0OTkwNTUyMTUxNDI1MTI0.XzrQhA.irgKpzb3tGVsx2_2nLFTHQDg9Nw";
                    $this->guildID = $_GET["guild_id"];
                    $this->authHeader = array( 'Authorization: Bot ' . $this->token );
                    echo $this->gateway = json_decode($this->getGateway(),true)["url"];
                    //echo $this->getGatewayBot();
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
                $this->authHeader = array( 'Authorization: Bearer ' . $this->token );
                }
                
                
            }else{
                if($bot){
                    $params = array(
                        'client_id' => $clientID,
                        'redirect_uri' => $this->redirectURL,
                        'response_type' => 'code',
                        'scope' => 'bot',
                        'permissions'=>"537529472"
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
            return $this->authHeader;
        }

        public function getGateway(){
            return Requests::getRequest($this->baseUrl."/gateway",$this->authHeader);
        }

        public function getGatewayBot(){
            return Requests::getRequest($this->baseUrl."/gateway/bot",$this->authHeader);
        }

        public function getUser(){
            //Gets info about user object
            return Requests::getRequest($this->baseUrl."/users/@me",$this->authHeader);
        }

        public function getCurrentUserGuilds(){
            //Gets info about servers that the current user is in
            return Requests::getRequest($this->baseUrl."/users/@me/guilds",$this->authHeader);
        }

        public function getUserConnections(){
            return Requests::getRequest($this->baseUrl."/users/@me/connections",$this->authHeader);
        }

        public function getGuildMembers($guild_ID){
            return Requests::getRequest($this->baseUrl."/guilds/".$guild_ID."/members",$this->authHeader);
        }

        public function getGuildChannels($guild_ID){
            return Requests::getRequest($this->baseUrl."/guilds/".$guild_ID."/channels",$this->authHeader);
        }
/*
        public function sendTestMessage($channelID,$content){
            //Requires identifying in wss gateway
            echo Requests::postRequest($this->baseUrl."/channels/".$channelID."/messages", array("content"=> "Hello, World!",
                                                                                            "tts"=> "false"
                                                                                            ),array_merge(array("Content-Type"=>"application/json"),$this->authHeader));
        }

        public function createWebHook($channelID){
            echo  Requests::postRequest($this->baseUrl."/channels/".$channelID."/webhooks",array("name"=>"JoTBoT"),array_merge(array("Content-Type"=>"application/json"),array($this->authHeader)));

        }
*/
        public function parseResponse($json_string){
            return json_decode($json_string,true);
        }
        
    }

    $api = new DiscordClient("744990552151425124","IvjLNMQxx4dXjcbUHFyvn7K4QOezxdr4","scopes to be filled later",true);
    

    $client = new Client("wss://gateway.discord.gg:443");
    $client->send("Hello from PHP");
    echo $client->receive() . "\n";
    $client->close();
    print_r(json_decode($api->sendTestMessage("744853637385420924","asdf"),true));

   
    
    // "genel" channel id = 744853637385420924
    // guild id = 744853637385420921