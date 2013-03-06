<?php

class SalmonDriver {
    
    static public function create() {
        return new SalmonDriver();
    }
    
    public function processBlubber($event, $activity) {
        OstatusPosting::createFromActivity($activity);
    }
    
    public function processFollowers($event, $activity) {
        OstatusContact::externalFollower($activity);
    }
    
}