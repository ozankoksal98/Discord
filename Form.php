<?php
    include "requests.php";
    
    class Form{
        private $apiKey = "fb164eb729c73fd5456f005f93a2715c";
        private $text_val;      //raw text form of get requests return value
        private $questions;     //form questions array
        private $properties;    //form properties array

        public function __construct($formID){
            $this->questions = json_decode(Requests::getRequest("https://api.jotform.com/form/".$formID."/questions?apiKey=".$this->apiKey,null),true);
            $this->properties  = json_decode(Requests::getRequest("https://api.jotform.com/form/".$formID."/properties?apiKey=".$this->apiKey,null),true);
        }
        public function getQuestions(){
            return $this->questions;
        }

        public function getProperties(){
            return $this->properties;
        }

    }
