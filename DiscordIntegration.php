<?php

    include "Form.php";

    class DiscordIntegration{
        private $partnerName = 'discord';
        private $token;
        private $user;
        private $formID;
        private $submissionID;
        private $api;

        private $answerData;
        private $questions;

        public function __construct($user, $formID, $submissionID){
            $this->user =$user ;
            $this->formID = $formID;
            $this->form = new Form($formID);
            $this->submissionID = $submissionID;
        }


    }