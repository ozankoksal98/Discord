<?php
    include_once "requests.php";
    
    class Form{
        private $apiKey = "fb164eb729c73fd5456f005f93a2715c";
        private $formInfo;
        private $title;
        private $text_val;      //raw text form of get requests return value
        private $questions;     //form questions array
        private $properties;    //form properties array



        public function __construct($formID){
            $this->formInfo = json_decode(Requests::getRequest("https://api.jotform.com/form/".$formID."?apiKey=".$this->apiKey,null),true)["content"];
            $this->questions = json_decode(Requests::getRequest("https://api.jotform.com/form/".$formID."/questions?apiKey=".$this->apiKey,null),true)["content"];
            $this->properties  = json_decode(Requests::getRequest("https://api.jotform.com/form/".$formID."/properties?apiKey=".$this->apiKey,null),true)["content"];
            $this->title = $this->formInfo["title"];
        }

        

        public function getQuestions(){
            return $this->questions;
        }

        public function getProperties(){
            return $this->properties;
        }

        public function getTitle()
        {
                return $this->title;
        }
    }
