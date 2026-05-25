<?php

return [
    'user_no' => env('EFT_USER_NO', '0000USERNO'),
    'user_shortname' => env('EFT_USER_SHORTNAME', 'USERSHORTNAME'),
    'user_longname' => env('EFT_USER_LONGNAME', 'USERLONGNAME'),

    'return_institution' => env('EFT_RETURN_INSTITUTION', '123'),
    'return_transit' => env('EFT_RETURN_TRANSIT', '12345'),
    'return_accountno' => env('EFT_RETURN_ACCOUNTNO', '1234567'),
];
