

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
    $formID = "202251323327039";
    $clientID = "";
    /*
    if (!isset($_GET["type"])) {
        $bot = new AuthorizeBot($clientID, "http://localhost/curl/Discord/index.php", $token);
    }*/
    
    $integration = new DiscordIntegration($formID, $token, 744853637385420921);

    echo '<div class="container-fluid">
    <div class="row">
      <div class="col bg-primary text-white">';

    //choose channel
    echo "Choose channel(s) to send submissions: <br>";
    echo '<form action="'.getReturnAddress().'" method="post">';
    foreach ($integration->getApi()->getTextChannels() as $channel => $v) {
        echo  '<div class="checkbox">
        <label><input type="checkbox" name="'.$v["id"].'" value = "channel" ">  '.$v["name"].'</label>
        </div>';
    };
    $chosenChannels = [];
    foreach ($_POST as $channel =>$v) {
        if ($v=="channel") {
            $chosenChannels[] = $channel;
        }
    }

    echo implode(",", $chosenChannels);
    echo '</div><div class="col bg-danger text-white">';
    echo "Choose fields you want to send: <br>";

    //choose fields
    $skippedFields = ["control_button","control_head","control_captcha","control_divider","control_text","control_image","control_inline"];
    $type;
    $usedFields = array();
    foreach ($integration->getForm()->getQuestions() as $question) {
        if (!in_array($question["type"], $skippedFields)) {
            echo  '<div class="checkbox">
            <label><input type="checkbox" name="'.$question["order"].'" value = "questions" >  '.$question["text"].'</label>
            </div>';
        } elseif ($question["type"]=="control_inline") {
            echo  '<div class="checkbox">
            <label><input type="checkbox" name="'.$question["order"].'" value = "questions" >  Fill the blanks </label>
            </div>';
        }
    }
    $chosenQuestions = [];
    foreach ($_POST as $question =>$v) {
        if ($v=="questions") {
            $chosenQuestions[] = $question;
        }
    }
    echo implode(",", $chosenQuestions);

    echo '</div>
    <div class="col bg-warning ">';
    //choose submissions
     echo "Choose submissions :<br>";
    foreach ($integration->getForm()->getSubmissions() as $subm) {
        $submID = $subm["id"];
        $submName;
        foreach ($subm["answers"] as $answer) {
            if ($answer["type"] == "control_fullname") {
                $submName = "Submission by : ".$answer["prettyFormat"];
                break;
            }
        }
        echo  '<div class="checkbox">
        <label><input type="checkbox" name="'.$submID.'" value = "submissions" >  '.$submName.'</label>
        </div>';
    }

    $chosenSubmissions = [];
    foreach ($_POST as $submission =>$v) {
        if ($v=="submissions") {
            $chosenSubmissions[] = $submission;
        }
    }
    echo implode(",", $chosenSubmissions);

    echo ' </div> </div> </div>
    <div class="row"> <div class="mx-auto" style="width: 200px;">
    <input type="submit" >
    </div></form></div>
    ';

    foreach ($chosenChannels as $channel) {
        foreach ($chosenSubmissions as $subm) {
            $integration->getApi()->sendMessage($channel, "", $integration->buildMessage($chosenQuestions, $formID, $subm));
        }
    }
