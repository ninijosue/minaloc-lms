<?php
// get current user point of service category

use core_course\customfield\course_handler;

    $userid = $USER->id;
    $moreuserinfo = $DB->get_record('additional_user_details', ['userid' => $userid]);
    $handler = course_handler::create();

    //  ;
    if($moreuserinfo){
        $servicepointcategory = $moreuserinfo->servicepointcategory; // Point of service
        // point_of_service_category names array
        $pointofservices = array(
            '1'=>"district",
            '2'=>"sector",
            '3'=>"cell",
            '4'=>"health",
            '5'=>"embassy",
            '6'=>"other",
            '7'=>"district_sector",
        );
        foreach ($courses as $courseid => $course) {
            $customfields = $handler->get_instance_data($courseid);

            $found = false;

            // foreach ($customfields as $fielddata) {
            //     $shortname = $fielddata->get_field()->get('shortname');
            //     if ($shortname == 'point_of_service_category') {
            //         $value = $fielddata->get_value();
            //         if($value !== "" && isset($pointofservices[$value])){
            //             $label = $pointofservices[$value];
                        
            //             if(str_contains($label, $servicepointcategory)){
            //                 echo "Point of Service found: $label user point of service $servicepointcategory";
            //                 $found = true;
            //                 break;
            //             }
            //             // if($label === $servicepointcategory){
            //             //     echo "Point of Service found: $label user point of service $servicepointcategory";
            //             //     $found = true;
            //             //     break;
            //             // }
            //         }
            //         else $found = true;
            //     }
            //     else{
            //         $found = true;
            //     }
            // }
            if(!$found){
                unset($courses[$courseid]);
            }
        }

    }