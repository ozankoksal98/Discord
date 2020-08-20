<?php

    include_once "Form.php";
    

    class DiscordIntegration{
        private $partnerName = 'discord';
        private $apiKey = "fb164eb729c73fd5456f005f93a2715c";
        private $user;
        private $formID;
        private $submissionID;
        private $api;
        private $form;
        

        public function __construct($user, $formID){
            $this->user =$user ;
            $this->formID = $formID;
            $this->form = new Form($formID);
            $this->submissionID = $this->getLastSubmission()["id"];
        }

        public function getForm()
        {
            return $this->form;
        }

        public function getApiKey(){
            return $this->apiKey;
        }


        public function getLastSubmission(){
            //returns array for last submission of form
            $temp = Requests::getRequest("https://api.jotform.com/form/".$this->formID."/submissions?apiKey=".$this->apiKey,null);
            return json_decode($temp,true)["content"]["0"];
        }

        public function listForms(){
            //only list enabled forms
            $formData = json_decode(Requests::getRequest("https://api.jotform.com/user/forms?".'filter=%7B"status"%3A"ENABLED"%7D',
                array("APIKEY: ".$this->apiKey)),true)["content"];

            foreach($formData as $f=>$v){
                echo $f." ) ";
                echo "Title: ". $v["title"] . "<br>";
                echo "Submission count: ". $v["count"] . "<br>";
                echo "Last submission: ". $v["last_submission"] . "<br>";
                echo "<br>";

            }
        }

        
        
        public function buildMessage(){
            $value = "You have received a submission for your form: ". $this->form->getTitle()."\n";
            $answers = $this->getLastSubmission()["answers"];
            
            usort($answers,function($a,$b){
                return intval($a["order"])- intval($b["order"]);
            });
            
            //print_r($answers);

            $skippedFields = ["control_button","control_head","control_captcha","control_divider","control_text","control_image"];
            foreach($answers as $key){
                if(!in_array($key["type"],$skippedFields)){
                    if(isset($key["prettyFormat"])){
                        if($key["type"]=="control_address"){
                            $value .= "**".$key["text"]."**"." : "."\n";
                            $value .= $key["answer"]["addr_line1"].",".$key["answer"]["addr_line2"]."\n";
                            $value .= $key["answer"]["city"].",".$key["answer"]["state"].",".$key["answer"]["postal"]."\n";
                            $value .= $key["answer"]["country"]."\n";

                        }else if($key["type"]=="control_datetime"){
                            $dateFormat = array();
                            foreach($key["answer"] as $k=>$v){
                                $dateFormat[] = $k;
                            }
                            $dateFormat = implode("-",$dateFormat);
                            $value .= "**".$key["text"]."**"." : ".$key["prettyFormat"]." , (".$dateFormat.")"."\n";

                        }else if($key["type"]=="control_inline"){
                            $value .=  "**"."Blanks :"."**"."\n";
                            $i =1 ;
                            foreach(array_values($key["answer"]) as $val){
                                $value .=  "Blank ".strval($i)." : ". $val . "\n";
                                $i++;
                            }

                        }else if($key["type"]=="control_fileupload"){
                            $url = $key["prettyFormat"];
                            $joinedValue= [];
                            $allLinks;
                            preg_match_all('/href=".*?"/', $url, $allLinks);
                            foreach($allLinks[0] as $link){
                                $link = str_replace(['href="','"'], '', $link);
                                array_push($joinedValue, $link);
                            }
                            
                            $value .= "**".$key["text"]."**"." :\n";
                            $value .= implode("\n",$joinedValue) . "\n";
                        }
                        
                        else if($key["type"]=="control_matrix"){
                            $value .= "**".$key["text"]."**"."\n";
                            foreach($key["answer"] as $k=>$v){
                                $value .= $k." : ".$v."\n";
                            }
                        }
                        
                        else if($key["type"]=="control_payment"){
                            $value .= "**".$key["text"]."**"."\n";
                            $payArr = json_decode($key["answer"]["paymentArray"],true);
                            foreach($payArr["product"] as $k=> $v){
                                $temp = explode("(",$v);
                                $value .= $temp[0];
                                $temp = explode(",",$temp[1]);
                                //print_r($temp);
                                if(count($temp)>1){
                                    $value .= "(".str_replace([" Quantity: ",")"],"",$temp[1])  .") : ";
                                    $value .= str_replace(["Amount: ",")"],"",$temp[0]). "\n";

                                }else{
                                    $value .= " : ".str_replace(["Amount: ",")"],"",$temp[0]) . "\n";
                                }
                            

                            };
                            $value .= "Total : ".$payArr["total"]." ".$payArr["currency"]."\n";
                            
                        }

                        else{
                            $value .= "**".$key["text"]."**"." : ".$key["prettyFormat"]."\n";
                        }
                        
                    }else{
                        if(is_array($key["answer"])){
                            $value .= "**".$key["text"]."**"." : ";
                            $value .= implode(", ",array_values($key["answer"]));
                            $value .= "\n";
                        }else{
                            $value .= "**".$key["text"]."**"." : ".$key["answer"]."\n";
                        }
                    }
                }else{
                }
            }
            return $value;
        }

        public function getApi()
        {
                return $this->api;
        }
    }




    

    //print_r($int->buildMessage());