<?php

return array(

    //prefix to each of the tables in the database
    'database_prefix' => 'ref_',

    //how many uses to pretend we have at the start.
    'start_at' => 1548,

    //the number of positions jumped when a referral is made.
    'jump_count' => 10,

    //How often to add an extra fake referral
    'addmore' => [
        'interval' => 3600, //the time between each insert
        'amount' => 1 //the amount to insert
    ]
);
