<?php

//let's see how much time it takes to render everything
$rendertime = microtime(true);
$rendertime = explode(' ',$rendertime);
$rendertime = $rendertime[1] + $rendertime[0];
$rendertimestart = $rendertime;

//check php version
if(phpversion() < '5') die("This web application requires PHP5 to run. You have PHP ".phpversion());

//our includes
@include_once('config.php');

//Do we have a config file? If not, time for the dreaded installation D:
if(!defined('LOTTO_SITE'))
    {
        $html = "<html><head><title>Lotto Site not Configured</title><style type='text/css'> H1 { text-align: center}</style></head><body>";
        $url = "http://".$_SERVER['HTTP_HOST'].'/install/';
        $html .= "<H1>Head to the <a href='".$url."'>Installation</a> to start installation.</H1>";
        die($html);
    }
//Is the install folder still there?! That is a no no. (to be fixed, doesn't work atm)
else if(file_exists("install"))
    {
        $html = "<html><head><title>Oh crap, it still exists</title></head><style type='text/css'> H1 { text-align: center}</style></head><body><H1>Delete your install folder</H1></body></html>";
    }
    
//Now for the fun stuff. Is there a lotto ID being requested?
$lottoid = $_GET['lottoid'];
$lottoid = trim($lottoid);
if(isset($lottoid))
    {
    //Is it empty?
    if(empty($lottoid))
        {
            echo '<a>No ID</a>';
        }
    //Is it a valid ID? MySQL time, oh yeah. (note, put in seperate include /
    //class
    else
        {
            echo '<a>ID is '.$lottoid.'</a>';
                        
        }
    }
//Goodbye, no ID
else
    {
        echo '<a>No ID</a>';
    }

//Ok how much time did that take to render?

$rendertime = microtime();
$rendertime = explode(' ', $rendertime);
$rendertime = $rendertime[1] + $rendertime[0];
$rendertimefinish = $rendertime;
$rendertimeresults = round(($rendertimefinish - $rendertimestart), 4);
echo '<br>Page generated in '.$rendertimeresults.' seconds</br>';

//This is kiades, we're done here.
?>