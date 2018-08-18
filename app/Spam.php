<?php

namespace App;

class Spam
{
    public function detect($body)
    {
        $this->detectInvalidKeywords($body);

        return false;
    }

    public function detectInvalidKeywords($body)
    {
        $invalidKeywords = [
            'something forbidden'
        ];

        foreach ($invalidKeywords as $invalidKeyword){
            if(stripos($body,$invalidKeyword) !== false){
                throw new \Exception('Your reply contains spam.');
            }
        }

    }
}