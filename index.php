<?php
     header("Access-Control-Allow-Origin: *");
    function getReturnAddress()
    {
        $returnURL = "http://localhost/curl/Discord/index.php?";
        $value= array();
        foreach ($_GET as $k=>$v) {
            $value[] = $k."=".$v;
        }
        return $returnURL .= implode("&", $value);
    }
   
    

    include_once "AuthorizeBot.php";
    include_once "DiscordIntegration.php";
    $token = "";
    $clientID = "";
    /*
    if (!isset($_GET["type"])) {
        $bot = new AuthorizeBot($clientID, "http://localhost/curl/Discord/index.php", $token);
    }
    */
    $integration = new DiscordIntegration($token, 744853637385420921);


    if (isset($_GET["type"])) {
        if ($_GET["type"]=="forms") {
            $filteredForms= [];
            foreach ($integration->getForms() as $k) {
                $filteredForms[] = ["id"=>$k["id"],"title"=>$k["title"],"url" => $k["url"]];
            }
            echo json_encode($filteredForms);
        } elseif ($_GET["type"]=="channels") {
            $ch = json_decode($integration->getApi()->getGuildChannels(), true);
            $filteredChannels = array();
            foreach ($ch as $k) {
                if ($k["type"]==0 || $k["type"]==5) {
                    $filteredChannels[] =["id"=>$k["id"],"type"=>$k["type"],"title"=>$k["name"],"parent_id"=>$k["parent_id"]];
                }
            }
            echo json_encode($filteredChannels);
        } elseif ($_GET["type"]=="fields") {
            $filteredQuestions = [];
            $skippedFields = ["control_button","control_head","control_captcha","control_divider","control_text","control_image","control_inline","control_widget"];
            foreach ($integration->getFormNew($_GET["formID"])->getQuestions() as $k) {
                if (isset($k["text"])) {
                    if (!in_array($k["type"], $skippedFields)) {
                        $filteredQuestions [] = ["id" => $k["order"],"title" => $k["text"], "type"=> $k["type"]];
                    }
                } else {
                    $filteredQuestions [] = ["id" => $k["order"],"title" => "name", "type"=> $k["type"]];
                }
            }
            
            echo json_encode($filteredQuestions);
        } elseif ($_GET["type"]=="submissions") {
            $filteredSubmissions = [];
            foreach ($integration->getFormNew($_GET["formID"])->getSubmissions() as $k) {
                $submName;
                foreach ($k["answers"] as $answer) {
                    if ($answer["type"] == "control_fullname") {
                        $submName = "Submission by : ".$answer["prettyFormat"];
                        break;
                    }
                }
                $filteredSubmissions [] = ["id" => $k["id"],"title" => $submName];
            }
            
            echo json_encode($filteredSubmissions);
        }
    } else {
        $post = json_decode(file_get_contents("php://input"), true);
        foreach ($post["channels"] as $channel) {
            foreach ($post["submissions"] as $subm) {
                $integration->getApi()->sendMessage($channel, "", $integration->buildMessage($post["fields"], $post["form"], $subm));
            }
        }
        //print return value
        echo(json_encode($post));
        //connect to database
        $conn = mysqli_connect("localhost", "ozan", "password", "discord_integration");
        //check connetion
        if (!$conn) {
            echo "connection failed: ". mysqli_connect_error();
        } else {
            //post query
            $username = mysqli_real_escape_string($conn, $post["username"]);
            $id = mysqli_real_escape_string($conn, $post["id"]);
            $guild_id = mysqli_real_escape_string($conn, $post["guild_id"]);
            $form = mysqli_real_escape_string($conn, $post["form"]);
            $channels = mysqli_real_escape_string($conn, implode(",",$post["channels"]));
            $fields = mysqli_real_escape_string($conn,implode(",",$post["fields"]));
            $submissions = mysqli_real_escape_string($conn,implode(",",$post["submissions"]));
            $sql = "INSERT INTO queries(user_id,guild_id,form_id,channels,fields,submissions)
            VALUES ('$id','$guild_id','$form','$channels','$fields','$submissions')";
            mysqli_query($conn,$sql);
            echo "query error". mysqli_error($conn);
            //check if user already exists
            //post user to users table
            $user_ids = mysqli_fetch_all(mysqli_query($conn, "SELECT user_id FROM users")) ;
            if (!in_array($id, $user_ids[0])) {
                if (mysqli_query($conn,"INSERT INTO users(user_id,user_name,guild_id)
                VALUES ('$id','$username','$guild_id')")) {
                    echo "query successful";
                } else {
                    echo "query error". mysqli_error($conn);
                }
            }
        }
    }
   
   
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
