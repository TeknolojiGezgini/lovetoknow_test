<?php
/*
Information and Usage:
    to use call sumAll() function with variables sumAll($postData, $depth_level, $unique);
    $postData : Client's/User's post data that includes file name
    $depth_level : how many times go deeper sub files,
    example: 
        A.txt -> 1, B.txt | B.txt -> 5, C.txt | C.txt -> 22, D.txt
    if $depth_level is 0 then it will just sum numbers in A.txt / if $depth_level is 2 then it will go from A.txt to B.txt then from B.txt to C.txt
    but not D.txt
    $unique : set 1 to enable 0 to disable | when enabled process same file only once even if it's included in multiple files

    note: in files negative numbers can be used as well ex: -25
*/

//Input for incoming file name by user (defined manually for test)
$_POST['file'] = 'A.txt';

function sumAll($postData, $depth_level, $unique){
    global $root_folder,
           $sub_files_single,
           $sub_files_all,
           $sub_files_diff,
           $errors,
           $numbers_total,
           $final_report;

    $sub_files_single = [];
    $sub_files_all = [];
    $sub_files_diff = [];
    $errors = [];
    $numbers_total = 0;
    $root_folder = './files/';
    $final_report = [];
    $run_count = 0;

    function sumSingleFile($file_name, $unique){
        global $root_folder,
               $sub_files_single,
               $sub_files_all,
               $sub_files_diff,
               $numbers_total,
               $errors,
               $final_report;

        $file_path = $root_folder . $file_name;
        $report = [];

        //Checks if file exists if not adds error report to errors array
        if(!file_exists($file_path))
            array_push($errors, ["error" => "File Doesn't Exist. error_code:1", "file_path" => $file_path]);
        
        //A report array is created for current file and it's name and path added to it
        $report['file_name'] = $file_name;
        $report['file_path'] = $file_path;

        //Gets file contents and stores it
        $file_content = file_get_contents($file_path);

        //Seperate items by end of line and create array with it | "PHP_EOL" provides system's current EOL(end of line)
        $file_content = explode(PHP_EOL, $file_content);

        //Sum all numbers in file and add to total of numbers ($numbers_total)
        $numbers_sum = array_sum($file_content);
        $numbers_total += $numbers_sum;
        //Current file's total of numbers added to it's report
        $report['numbers_sum'] = $numbers_sum;

        //Checks if file includes another file if yes it added to $sub_files_single array
        foreach($file_content as $key => $value){
            if(strpos($value, '.txt')){
                // will be removed!!!
                array_push($sub_files_single, $value);
                //Current file's sub files added to it's report
                $report['sub_files'][] = $value;
            }
        }

        //Unique: set 1 to enable 0 to disable
        //It Filters out same files with array_unique() if there are any and picks file names from $sub_files_single which is not included in $sub_files_all
        if($unique){
            $sub_files_single = array_unique($sub_files_single);
            $sub_files_diff = array_diff($sub_files_single,$sub_files_all);
        }else
            $sub_files_diff = $sub_files_single;


        //Created complete report is added to final report array which is holding reports of all files
        $final_report['files'][] = $report;
    }
    //end of sumSingleFile() function

    //First run for main file
    sumSingleFile($postData['file'], $unique);
    foreach($sub_files_single as $key => $value)
        sumSingleFile($value, $unique);
    //Adding new sub files to $sub_files_all and unsetting $sub_files_single |to prevent summing same file multiple times
    $sub_files_all = array_merge($sub_files_all, $sub_files_diff);
    $sub_files_single = [];

    //Second run for files included in main file gets looped to go deeper sub files as many as declared in $depth_level variable
    while($run_count<$depth_level){
        //call sumSingleFile() for every sub file
        foreach($sub_files_diff as $key => $value)
            sumSingleFile($value, $unique);
        //Adding new sub files to $sub_files_all and unsetting $sub_files_single |to prevent summing same file multiple times
        $sub_files_all = array_merge($sub_files_all, $sub_files_diff);
        $sub_files_single = [];

        $run_count++;
    }

    //if there are errors return error
    if(!empty($errors))
        return $errors;

    //else adds total of numbers and sub files list to final report and return final report
    $final_report['numbers_total'] = $numbers_total;
    $final_report['files_total'] = $sub_files_all;
    return $final_report;
}
//end of the sumAll() function

//<pre> tag can be used for cleaner look of report array; or it can be printed as json with json_encode() function
//USAGE: sumAll($postData, $depth_level, $unique) | $postData: $_POST input, $depth_level: int value, $unique: 1 or 0
//Example:
//        sumAll($_POST, 0, 0) | Just sums main file
//        sumAll($_POST, 1, 0) | sums main file and it's sub files (same file can be proccessed multiple times)
//        sumAll($_POST, 2, 0) | sums main file, it's sub files and subs of sub files (same file can be proccessed multiple times)
//        sumAll($_POST, 5, 0) | sums up to main_file->subs->subs->subs->subs->subs (same file can be proccessed multiple times)
//        sumAll($_POST, 2, 1) | sums main file, it's sub files and subs of sub files (same file will be proccessed only once)
print '<pre>';
print_r( sumAll($_POST, 2, 0) );
print '</pre>';