<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
        integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">

    <title>Hello, world!</title>
</head>

<body>

    <html>

    <body>

    <ul>
  <li class="dropdown">
    <a  data-toggle="dropdown" class="dropdown-toggle">
      Dropdown Form<b class="caret"></b>
    </a>
    <ul class="dropdown-menu">
      <li><label class="checkbox"><input type="checkbox">Two</label></li>
      <li><label class="checkbox"><input type="checkbox">Two</label></li>
    </ul>
  </li>
</ul>




    </body>

    </html>
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"
        integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"
        integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous">
    </script>
</body>

</html>

<?php

    function getReturnAddress(){
        $returnURL = "http://localhost/curl/index.php?";
        $value= array();
        foreach($_GET as $k=>$v){
                $value[] = $k."=".$v;

        }
        return $returnURL .= implode("&",$value);
    }

    include_once "AuthorizeBot.php";
    include_once "DiscordIntegration.php";
    $token = "";
    $formID = "202314428827050";
    $clientID = "";

    $bot = new AuthorizeBot($clientID,"http://localhost/curl/index.php",$token);
    $integration = new DiscordIntegration($formID,$token,$_GET["guild_id"]);

    echo '<div class="container-fluid">
    <div class="row">
      <div class="col bg-primary text-white">';

    //choose channel
    echo "Choose channel(s) to send submissions: <br>";
    echo '<form action="'.getReturnAddress().'" method="post">';
    foreach($integration->getApi()->getTextChannels() as $channel => $v){
        echo  '<div class="checkbox">
        <label><input type="checkbox" name="'.$v["id"].'" value = "channel" ">  '.$v["name"].'</label>
        </div>';
    };
    $chosenChannels = [];
    foreach($_POST as $channel =>$v){
        if($v=="channel"){
            $chosenChannels[] = $channel;
        }
    }

    echo implode(",",$chosenChannels);
    echo '</div><div class="col bg-danger text-white">';
    echo "Choose fields you want to send: <br>";

    //choose fields
    $skippedFields = ["control_button","control_head","control_captcha","control_divider","control_text","control_image","control_inline"];
    $type;
    $usedFields = array();
    foreach($integration->getForm()->getQuestions() as $question){
        if(!in_array($question["type"],$skippedFields)){
            echo  '<div class="checkbox">
            <label><input type="checkbox" name="'.$question["order"].'" value = "questions" >  '.$question["text"].'</label>
            </div>';
        }else if($question["type"]=="control_inline") {
            echo  '<div class="checkbox">
            <label><input type="checkbox" name="'.$question["order"].'" value = "questions" >  Fill the blanks </label>
            </div>';
        }
    }
    $chosenQuestions = [];
    foreach($_POST as $question =>$v){
        if($v=="questions"){
            $chosenQuestions[] = $question;
        }
    }   
    echo implode(",",$chosenQuestions);

    echo '</div>
    <div class="col bg-warning ">';
    //choose submissions
     echo "Choose submissions :<br>";
    foreach($integration->getForm()->getSubmissions() as $subm){
        $submID = $subm["id"];
        $submName;
        foreach($subm["answers"] as $answer){
            if($answer["type"] == "control_fullname"){
                $submName = "Submission by : ".$answer["prettyFormat"];
                break;
            }
        }
        echo  '<div class="checkbox">
        <label><input type="checkbox" name="'.$submID.'" value = "submissions" >  '.$submName.'</label>
        </div>';
    }

    $chosenSubmissions = [];
    foreach($_POST as $submission =>$v){
        if($v=="submissions"){
            $chosenSubmissions[] = $submission;
        }
    }
    echo implode(",",$chosenSubmissions);

    echo ' </div> </div> </div> 
    <div class="row"> <div class="mx-auto" style="width: 200px;">
    <input type="submit" >
    </div></form></div>  
    ';

    foreach($chosenChannels as $channel){
        foreach($chosenSubmissions as $subm){
            $integration->getApi()->sendMessage($channel,"",$integration->buildMessage($chosenQuestions,$formID,$subm));
        }
    }

   
    //format messages to embed and 
    //send chosen submissions 

    /*
    This was discussed at some length on the Discord API server when this issue was opened, but I'll reiterate my thoughts on this here.

First, that's right, the documentation is explicitly only for making API calls with Bot-type and Bearer-type tokens. User tokens, and endpoints that only user accounts can use are not / will not be documented.

So, if this is to suggest we add some kind of marking to display if a route is Bot or Bearer only, I think this isn't really worth it / would be redundant because:

All endpoints are for Bot type tokens (unless explicitly said otherwise) and
Any route that accepts a Bearer token is already explicitly documented as such (example: Accept Invite)
The routes that you are granted through the OAuth2 scopes are already explicitly listed on the OAuth2 reference page, and the user shouldn't assume that you get access to anything more than what is described on that page.
So such a "bot indicator" would nearly be on every single route, and a "bearer indicator" would only be on a few (and again, they already are all listed in a single place on the OAuth2 page)

On the other hand, for example, a lot of users seem to make the assumption that the guilds scope implicitly grants GET /guilds/{gid}, when however it explicitly states that you only get access to GET /users/@me/guilds as it says. Maybe there's something to clarify there? (I don't think so)

The exception to this may be RPC via rpc.api. But if I recall, that's waiting some additional documentation anyways (see: Proxied API Requests).
     */
?>