<?php

#
# UTIL PHP CLASS
# Useful functions for PHP Developer
# https://github.com/j84/util_php_class
#
# - util::sort2DimArray : sort two dimensions array
# - util::explode_and_get : axplode an array and get a value by index
# - util::cutStr : cut a string
# - util::strToUrl : convert string to url
# - util::isEmail : email checking
# - util::sendEmail : send a simple email
# - util::getFileExtension : get extension of file
# - util::scanDir : list a directory
# - util::writeJsonFile : create or update a json file
# - util::readJsonFile : read and decode a json file
# - util::uploadFile : upload a file
# - util::formatDate : convert date to FR format
# - util::addDayToDate : adds or removes a period to a date
# - util::htmlTable : make HTML table
# - util::htmlSelect : make select form element
# - util::isMultiple : check if a number is multiple
#

class util{
    
    #
    # util::sort2DimArray : sort two dimensions array
    # Param : $data = two dimensions array to sort, $key = index for sorting, $mode = sorting mode (desc, asc or num)
    # Return : same array sorted
    #
    public static function sort2DimArray($data, $key, $mode){
        if($mode == 'desc') $mode = SORT_DESC;
        elseif($mode == 'asc') $mode = SORT_ASC;
        elseif($mode == 'num') $mode = SORT_NUMERIC;
        $temp = array();
        foreach($data as $k=>$v){
            $temp[$k] = $v[$key];
        }
        array_multisort($temp, $mode, $data);
        return $data;
    }
	
	#
    # util::explode_and_get : axplode an array and get a value by index
    # Param : $str = string to explode, $separator = separator for explode function, index to get (0, 1, 2...)
    # Return : value of array
    #
	public function explode_and_get($str, $separator, $key = 0){
		$temp = @explode($separator, $str);
		if(is_array($temp)) return $temp[$key];
		else return $temp;
	}
    
    #
    # util::cutStr : cut a string
    # Param : $str = string to cut, $length = number of characters before the break, $add = end string
    # Return : same string ou cut string
    #
    public static function cutStr($str, $length, $add = '...'){
        if(mb_strlen($str) > $length) $str = mb_strcut($str, 0, $length).$add;
        return $str;
    }
    
    #
    # util::strToUrl : convert string to url
    # Param : $str = string to convert
    # Return : simplified string
    #
    public static function strToUrl($str){
        $str = str_replace('&', '-', $str);
        if($str !== mb_convert_encoding(mb_convert_encoding($str,'UTF-32','UTF-8'),'UTF-8','UTF-32')) $str = mb_convert_encoding($str,'UTF-8');
        $str = htmlentities($str, ENT_NOQUOTES ,'UTF-8');
        $str = preg_replace('`&([a-z]{1,2})(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig);`i','$1',$str);
        $str = preg_replace(array('`[^a-z0-9]`i','`[-]+`'),'-',$str);
        return strtolower(trim($str,'-'));
    }
    
    #
    # util::isEmail : email checking
    # Param : $email = email
    # Return : true or false
    #
    public static function isEmail($email){
        if(preg_match("/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,4}$/", $email)) return true;
        return false;
    }
    
    #
    # util::sendEmail : send a simple email
    # Param : $from = from email, $reply = reply email, $to = to email, $subject = subject of email, $msg = content of email, $type = type (text/plain or text/html)
    # Return : true or false
    #
    public static function sendEmail($from, $reply, $to, $subject, $msg, $type = 'text/html'){
    	$headers = "From: ".$from."\r\n";
    	$headers.= "Reply-To: ".$reply."\r\n";
    	$headers.= "X-Mailer: PHP/".phpversion()."\r\n";
    	$headers.= 'Content-Type: '.$type.'; charset="utf-8"'."\r\n";
    	$headers.= 'Content-Transfer-Encoding: 8bit';
    	if(@mail($to, $subject, $msg, $headers)) return true;
    	return false;
    }
    
    #
    # util::getFileExtension : get extension of file
    # Param : $file = path of file
    # Return : string
    #
    public static function getFileExtension($file){
        return substr(strtolower(strrchr(basename($file), ".")), 1);
    }
    
    #
    # util::scanDir : list a directory
    # Param : $folder = path of folder, $not = files to ignore
    # Return : array with dir index and file index
    #
    public static function scanDir($folder, $not = array()){
        $data['dir'] = array();
        $data['file'] = array();
        foreach(scandir($folder) as $file){
            if($file[0] != '.' && !in_array($file, $not)){
                if(is_file($folder.$file)) $data['file'][] = $file;
                elseif(is_dir($folder.$file)) $data['dir'][] = $file;
            }
        }
        return $data;
    }
    
    #
    # util::writeJsonFile : create or update a json file
    # Param : $file = path of file, $data = data to convert and save in json file
    # Return : true or false
    #
    public static function writeJsonFile($file, $data){
        if(@file_put_contents($file, json_encode($data), LOCK_EX)) return true; 
    	return false;
    }
    
    #
    # util::readJsonFile : read and decode a json file
    # Param : $file = path of file, $assoc = for convert data to array (true or false)
    # Return : data
    #
    public static function readJsonFile($file, $assoc = true){
        return json_decode(@file_get_contents($file), $assoc);
    }
    
    #
    # util::uploadFile : upload a file
    # Param : $k = key of $_FILES array, $dir = directory path for upload, $name = name for the file, $validation = array of autorized extensions files and max size file
    # Return : result (extension error, size error, success or undefined)
    #
    public static function uploadFile($k, $dir, $name, $validations = array()){
        if(isset($_FILES[$k]) && $_FILES[$k]['name'] != ''){
            $extension = mb_strtolower(util::getFileExtension($_FILES[$k]['name']));
            if(isset($validations['extensions']) && !in_array($extension, $validations['extensions'])) return 'extension error';
            $size = filesize($_FILES[$k]['tmp_name']);
            if(isset($validations['size']) && $size > $validations['size']) return 'size error';
            if(move_uploaded_file($_FILES[$k]['tmp_name'], $dir.$name.'.'.$extension)) return 'success';
            else return 'upload error';
        }
        return 'undefined';
    }
    
    #
    # util::formatDate : convert date to FR format
    # Param : $date = US date
    # Return : date converted
    #
    public static function formatDate($date, $langFrom = 'en', $langTo = 'fr'){
        $date = substr($date, 0, 10);
        $temp = preg_split('#[-_;\. \/]#', $date);
        if($langFrom == 'en'){
            $year = $temp[0];
            $month = $temp[1];
            $day = $temp[2];
        }
        elseif($langFrom == 'fr'){
            $year = $temp[2];
            $month = $temp[1];
            $day = $temp[0];
        }
        if($langTo == 'en') $data = $year.'-'.$month.'-'.$day;
        elseif($langTo == 'fr') $data = $day.'/'.$month.'/'.$year;
        return $data;
    }
	
	#
    # util::addDayToDate : adds or removes a period to a date
    # Param : $add = period (+1 days, -3 months...)
    # Return : date (now or US date)
    #
    public static function addDayToDate($add = '+1 days', $date = 'now'){
		if($date == 'now') $date = date('Y-m-d');
        return date('Y-m-d', strtotime($date.' '.$add));
    }
    
    #
    # util::htmlTable : make HTML table
    # Param :
    # Return :
    #
    public static function htmlTable($cols, $vals, $params = ''){
        $cols = explode(',', $cols);
        $data = '<table '.$params.'><thead><tr>';
        foreach($cols as $v) $data.= '<th>'.$v.'</th>';
        $data.= '</tr></thead><tbody>';
        foreach($vals as $v){
            $data.= '<tr>';
            foreach($v as $v2) $data.= '<td>'.$v2.'</td>';
            $data.= '</tr>';
        }
        $data.= '</tbody><tfoot><tr>';
        foreach($cols as $v) $data.= '<th>'.$v.'</th>';
        $data.= '</tr></tfoot></table>';
        return $data;
    }
    
    #
    # util::htmlSelect : make select form element
    # Param :
    # Return :
    #
    public static function htmlSelect($options, $selected = '', $params = ''){
    	$data = '<select '.$params.'>';
    	foreach($options as $k=>$v) $data.= '<option value="'.$k.'"'.(($k == $selected) ? ' selected="selected"' : '').'>'.$v.'</option>';
    	$data.= '</select>';
    	return $data;
    }
	
	#
    # util::isMultiple : check if a number is multiple
    # Param : $number = number to check, $multipleOff = multiple off ??
    # Return : true or false
    #
    public static function isMultiple($number, $multipleOff){
    	if($number % $multipleOff == 0) return true;
		return false;
    }
    
}

//var_dump(util::addDayToDate('2016-12-01', '-1 months'));

?>