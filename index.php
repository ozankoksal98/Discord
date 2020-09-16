<?php
     header("Access-Control-Allow-Origin: *");
    
    require_once "src/AuthorizeBot.php";
    require_once "src/DiscordIntegration.php";

    $token = "NzQ1NjcwOTUzMTc4MjM0OTQw.Xz1KMQ.GFrdHi6lengxhqp4sAYvC-WzFz0";
    $clientID = "745670953178234940";
    $integration = new DiscordIntegration($token, 744853637385420921, "fb164eb729c73fd5456f005f93a2715c");


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
        $guild_members = json_decode($integration->getApi()->getGuildMembers(), true);
        foreach ($guild_members as $k) {
            if (!isset($k["user"]["bot"])) {
                $filteredChannels[] =["id"=>$k["user"]["id"],"type"=>"1","title"=>$k["user"]["username"],"parent_id"=>"0"];
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
                if ($answer["type"] == "control_fullname" && isset($answer["prettyFormat"])) {
                    $submName = "Submission by : ".$answer["prettyFormat"];
                    break;
                }
            }
            $filteredSubmissions [] = ["id" => $k["id"],"title" => $submName];
        }
            
        echo json_encode($filteredSubmissions);
    } elseif ($_GET["type"]=="history") {
        $conn = mysqli_connect("localhost", "ozan", "password", "discord_integration");
        $result = mysqli_query($conn, "SELECT id,user_id,guild_id,form_id,channels,users,fields,notes,submissions,created_at FROM queries WHERE user_id = ".$_GET['user_id']);
        $val = mysqli_fetch_all($result, MYSQLI_ASSOC);
        foreach ($val as $k=>$v) {
            $ch = json_decode($v["channels"], true);
            $form = json_decode($v["form_id"], true);
            $field =json_decode($v["fields"], true);
            $sub = json_decode($v["submissions"], true);
            $users =  json_decode($v["users"], true);
            $notes = json_decode($v["notes"], true);
            $val[$k]["channels"] = $ch;
            $val[$k]["form_id"] = $form;
            $val[$k]["fields"] = $field;
            $val[$k]["submissions"] = $sub;
            $val[$k]["users"] = $users;
            $val[$k]["notes"] = $notes;
            //$k["channels"] = "asdfasd";
        }
        echo json_encode($val);
    }
} else {
    $post = json_decode(file_get_contents("php://input"), true);
    $fields = [];
    foreach (($post["fields"]) as $field) {
        $fields[] =  $field["id"];
    }
    if ($post["type"] == "preview") {
        echo  $integration->buildMessage($fields, $post["form"][0]["id"], $post["submissions"][0]["id"], $post["notes"]);
    } elseif ($post["type"] == "submit") {
        print_r($post);
        foreach (($post["channels"]) as $channel) {
            foreach (($post["submissions"]) as $subm) {
                $integration->getApi()->sendMessage($channel["id"], "", $integration->buildMessage($fields, $post["form"][0]["id"], $subm["id"], $post["notes"]));
            }
        }
        
        foreach (($post["users"]) as $user) {
            $dm_channel = json_decode($integration->getApi()->createDmChannel($user["id"]), true)["id"];
            foreach (($post["submissions"]) as $subm) {
                $integration->getApi()->sendMessage($dm_channel, "", $integration->buildMessage($fields, $post["form"][0]["id"], $subm["id"], $post["notes"]));
            }
        }
        

        $conn = mysqli_connect("localhost", "ozan", "password", "discord_integration");
        if (!$conn) {
            echo "connection failed: ". mysqli_connect_error();
        } else {
            //post query
            $username =  $post["username"];
            $id =  $post["id"];
            $guild_id =  $post["guild_id"];
            $form =  json_encode($post["form"]);
            $channels =  json_encode($post["channels"]);
            $fields = json_encode($post["fields"]);
            $submissions = json_encode($post["submissions"]);
            $users = json_encode($post["users"]);
            $notes = json_encode($post["notes"]);
            $sql = "INSERT INTO queries(user_id,guild_id,form_id,channels,users,fields,notes,submissions)
            VALUES ('$id','$guild_id','$form','$channels','$users','$fields','$notes','$submissions')";
            mysqli_query($conn, $sql);
            echo "query error". mysqli_error($conn);
            //check if user already exists
            //post user to users table
            $user_ids = mysqli_fetch_all(mysqli_query($conn, "SELECT user_id FROM users"));
            if (!in_array($id, $user_ids[0])) {
                if (mysqli_query(
                    $conn,
                    "INSERT INTO users(user_id,user_name,guild_id)
                VALUES ('$id','$username','$guild_id')"
                )
                ) {
                    echo "query successful";
                } else {
                    echo "query error ,cant add user". mysqli_error($conn);
                }
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
