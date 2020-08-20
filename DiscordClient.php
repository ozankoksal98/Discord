
<?php

include_once "requests.php";
include_once "DiscordIntegration.php";
//to-do implement splitting longer messages
class DiscordClient{
    private $baseUrl = "https://discord.com/api";
    private $token = "NzQ0OTkwNTUyMTUxNDI1MTI0.XzrQhA.3pBq_Rs899Lev-0npCEN3nF5N3E";
    private $authHeader ;
    private $guildID ;

    public function __construct(){
        $this->authHeader = 'Authorization: Bot ' . $this->token;
        $this->guildID = $_GET["guild_id"];
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
            "tts": "false"
        }',true),array("Content-Type: multipart/form-data",$this->getauthHeader()));
    }

    public function sendMessage($channelID,$content){
    
    //Send custom message
    //can only send text up to 2000 characters
        echo Requests::postRequest($this->baseUrl."/channels/".$channelID."/messages", array("content"=>$content,"tts"=> "false"),
            array("Content-Type: multipart/form-data",$this->getauthHeader()));
    }

    public function parseResponse($json_string){
        return json_decode($json_string,true);
    }

    public function listChannels(){
        $channelArr = json_decode($this->getGuildChannels(),true);
        foreach($channelArr as $channel=>$v){
            //remove all channels and categories except text channels
            if($v["type"]!="0"){
                unset($channelArr[$channel]);
            }
        }
        usort($channelArr,function($a,$b){
            return intval($a["position"])-intval($b["position"]);
        });

        foreach($channelArr as $channelnum=>$v){
            
            echo $channelnum.") ".$v["name"]."<br>";
            
        }
        return $channelArr;
    }
    
}
$client = new DiscordClient();
$int = new DiscordIntegration("user","202314428827050");
$str = $int->buildMessage();
echo str_replace("\n","<br>",$str);

$client->sendMessage("745645476589600830",$str);

// "genel" channel id = 744853637385420924
// guild id = 744853637385420921
?>