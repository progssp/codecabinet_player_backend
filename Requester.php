<?php
    // require_once(__DIR__.'/connect.php');
    require_once(__DIR__.'/VideoStreamer.php');
    // require_once(__DIR__.'/Utility.php');

    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Expose-Headers: Content-Range");

    if($_SERVER['REQUEST_METHOD'] == "GET"){
        if(isset($_GET['call_func'])){           
            if($_GET['call_func'] == "file_stream"){
                //header("Content-type: application/json");
                $is_header_info_found = 0;
                $vid_title_from_header = NULL;

                if(function_exists('apache_request_headers')){
                    $headers = apache_request_headers();
                    foreach($headers as $key => $val){
                        if(($key == 'vid_title')||($key == 'Vid_title')){
                            $is_header_info_found = 1;
                            $vid_title_from_header = $val;
                            break;
                        }
                    }

                    if($is_header_info_found == 1){
                        $video_path = __DIR__.'/videos/'.$vid_title_from_header;
                        // $video_path = __DIR__.'/videos/introduction_to_programming.mp4';
                        $streamer = new VideoStream($video_path);
                        $streamer->start();
                    }
                    else{
                        echo 'no head found';
                    }
                }
            }
            else if($_GET['call_func'] == "file_real_stream"){
                //header("Content-type: application/json");
                // $is_header_info_found = 0;
                // $vid_title_from_header = NULL;

                if(function_exists('apache_request_headers')){
                    $headers = apache_request_headers();
                    foreach($headers as $key => $val){
                        //echo $key . " : " . $val;
                        //echo "<br/>";
                        if(($key == 'vid_title')||($key == 'Vid_title')){
                            $is_header_info_found = 1;
                            $vid_title_from_header = $val;
                            break;
                        }
                    }
                }

                    // if($is_header_info_found == 1){
                        $video_path = __DIR__.'/videos/'.$vid_title_from_header;
                        //echo $video_path;
                        
                        // $video_path = __DIR__.'/videos/a9b1bee779f6929fe350575469afa5768bc319b4_new.webm';
                        $streamer = new VideoStream($video_path);
                        $streamer->start();
                    // }
                    // else{
                    //     echo 'no head found';
                    // }
                //}
            }
            else{
                // header("Content-type: application/json");
                echo json_encode([
                    'status' => false,
                    'msg' => 'func val present but no match in get request'
                ]);
            }
        }
        else{
            header("Content-type: application/json");
            echo json_encode([
                'status' => false,
                'msg' => 'func val not present in get request'
            ]);
        }
    }
    else{
        if(isset($_POST['call_func'])){
            if($_POST['call_func'] == "add_video"){
                if(!isset($_POST['video_name'])){
                    header("Content-type: application/json");
                    echo json_encode([
                        'status' => false,
                        'msg' => 'enter video name'
                    ]);
                    die();
                }
                //sanitizing the input
                $video_name = $_POST['video_name'];
                $video_name = trim($video_name);
                $video_name = strtolower($video_name);
                $video_name = ucfirst($video_name);
                $vid_name_identifier = generateUniqueVidNameIdentifier();
                               
                require('./connect.php');
                date_default_timezone_set('Asia/Kolkata');
                $timestamp = date('Y-m-d H:i:s');
                $qry = "insert into vtech_videos values(0,'".$video_name."','".$vid_name_identifier."','".$timestamp."','".$timestamp."',0,'0000-00-00')";
                $res = mysqli_query($con, $qry);
                if($res > 0){
                    header("Content-Type: application/json");
                    echo json_encode([
                        'status' => true,
                        'msg' => 'record added'
                    ]);
                }
                else{
                    header("Content-Type: application/json");
                    echo json_encode([
                        'status' => false,
                        'msg' => 'error'
                    ]);
                }
                require(__DIR__.'/close_connect.php');
                
            }
            else if($_POST['call_func'] == "load_videos"){
                
                $qry = "select * from vtech_videos order by id desc";
                require(__DIR__.'/connect.php');
                $result = mysqli_query($con, $qry);
                //$res = mysqli_fetch_assoc($result);
                while($res = mysqli_fetch_assoc($result)){
                    $ret_arr[] = $res;
                }
                header("Content-Type: application/json");
                echo json_encode($ret_arr);

                require(__DIR__.'/close_connect.php');
            }
            else{
                header("Content-type: application/json");
                echo json_encode([
                    'status' => false,
                    'msg' => 'func val present but no match in post request'
                ]);   
            }
        }
        else{            
            header("Content-type: application/json");
            echo json_encode([
                'status' => false,
                'msg' => 'func val not present in post request'
            ]);
        }
    }


    function generateUniqueVidNameIdentifier(){
        $return_str = sha1(uniqid());

        require(__DIR__.'/connect.php');
        $qry = "select id from vtech_videos where video_name_identifier='".$return_str."'";
        $result = mysqli_query($con, $qry);
        if(mysqli_num_rows($result) > 0){
            require(__DIR__.'/close_connect.php');
            generateUniqueVidNameIdentifier();
        }
        else{
            require(__DIR__.'/close_connect.php');
            return $return_str;
        }        
    }


    function rangeDownload($file){
        $fp = @fopen($file, 'rb');

        $size   = filesize($file); // File size
        $length = $size;           // Content length
        $start  = 0;               // Start byte
        $end    = $size - 1;       // End byte
        // Now that we've gotten so far without errors we send the accept range header
        /* At the moment we only support single ranges.
        * Multiple ranges requires some more work to ensure it works correctly
        * and comply with the spesifications: http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
        *
        * Multirange support annouces itself with:
        * header('Accept-Ranges: bytes');
        *
        * Multirange content must be sent with multipart/byteranges mediatype,
        * (mediatype = mimetype)
        * as well as a boundry header to indicate the various chunks of data.
        */
        header("Accept-Ranges: 0-$length");
        // header('Accept-Ranges: bytes');
        // multipart/byteranges
        // http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
        if (isset($_SERVER['HTTP_RANGE'])){
            $c_start = $start;
            $c_end   = $end;

            // Extract the range string
            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            // Make sure the client hasn't sent us a multibyte range
            if (strpos($range, ',') !== false){
                // (?) Shoud this be issued here, or should the first
                // range be used? Or should the header be ignored and
                // we output the whole content?
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                // (?) Echo some info to the client?
                exit;
            } // fim do if
            // If the range starts with an '-' we start from the beginning
            // If not, we forward the file pointer
            // And make sure to get the end byte if spesified
            if ($range[0] == '-'){
                // The n-number of the last bytes is requested
                $c_start = $size - substr($range, 1);
            } else {
                $range  = explode('-', $range);
                $c_start = $range[0];
                $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
            } // fim do if
            /* Check the range and make sure it's treated according to the specs.
            * http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
            */
            // End bytes can not be larger than $end.
            $c_end = ($c_end > $end) ? $end : $c_end;
            // Validate the requested range and return an error if it's not correct.
            if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size){
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                // (?) Echo some info to the client?
                exit;
            } // fim do if

            $start  = $c_start;
            $end    = $c_end;
            $length = $end - $start + 1; // Calculate new content length
            fseek($fp, $start);
            header('HTTP/1.1 206 Partial Content');
        } // fim do if

        // Notify the client the byte range we'll be outputting
        header("Content-Range: bytes $start-$end/$size");
        header("Content-Length: $length");

        // Start buffered download
        $buffer = 1024 * 8;
        while(!feof($fp) && ($p = ftell($fp)) <= $end){
            if ($p + $buffer > $end){
                // In case we're only outputtin a chunk, make sure we don't
                // read past the length
                $buffer = $end - $p + 1;
            } // fim do if

            set_time_limit(0); // Reset time limit for big files
            echo fread($fp, $buffer);
            flush(); // Free up memory. Otherwise large files will trigger PHP's memory limit.
        } // fim do while

        fclose($fp);
    } // fim do function
?>