<?php

    $lang = $_GET['language'] ?? 'eng';

        switch ($lang){
            case "eng":
                $data['privacy_text'] = file_get_contents('./localization/lang_en.txt', true);
                break;
            case "aze":
            $data['privacy_text'] = file_get_contents('./localization/lang_az.txt', true);
                break;
            case "uzb":
                $data['privacy_text'] = file_get_contents('./localization/lang_uz.txt', true);
                break;
        }

?>