<?php

//Make sure the API is on before requesting any XML file. Spits out the filename once done
function getxml($url)
{
    $hashedurl = md5($url);
    if (file_exists($hashedurl))
        {
            if (iscachetimerup($hashedurl))
                {
                    unlink($hashurl);
                    //  Use cURL to download the data into a variable
                    $curl_result = file_get_contents($url);
                    //  Save that data to a file.
                    $fp = fopen($hashedurl, 'w');
                    fwrite ( $fp , $curl_result );
                    fclose($fp);
                }
            else
                {
                    $curl_result = file_get_contents($hashedurl);
                }
            
        }
    else
        {
            //  Use cURL to download the data into a variable
            $curl_result = file_get_contents($url);
            //  Save that data to a file.
            $fp = fopen($hashedurl, 'w');
            fwrite ( $fp , $curl_result );
            fclose($fp);
        }
    $xmlfilename = $hashedurl;
    return($xmlfilename);
}

//Is the API server online?
function check_api_online()
    { 
	$serverstatus_url = "http://api.eve-online.com/server/ServerStatus.xml.aspx";
	if( !defined('API_ONLINE') )
            {
		$online = false;
		if( $XmlSource = getxml($serverstatus_url))
                    {
			$xml = simplexml_load_string($XmlSource);
                        if( strtolower( (string) @$xml->result->serverOpen) == 'true' )
                            {
				$online = true;
                            }
                    } 
		define('API_ONLINE',$online);
            }
	return API_ONLINE;
    }
    
    function doeswalletentryexist($referenceID)
    //once working, put in secondary input to say if it wants the value to be turned.
    {
        if (!$con = connectDB())
            {
                die ('Could not connect to the database server' . mysqli_connect_error());
            }

        $referencequery = "SELECT refID FROM walletjournal WHERE refID LIKE '" . $referenceID . "'";
        $referenceresult = mysqli_query($con,$referencequery);
        $refIDrowresult = $referenceresult->fetch_object();
        if ($refIDrowresult->refID == $referenceID)
            {
                //It found an entry, which returns a true statement back
                return TRUE;
            }
        else
            {
                //We didn't find anything, which will return a failed statment back
                return FALSE;
            }
        
    }

//needs to be updated / improved, needs to be on the same time as the servers...
//Note, major time drifts needed to be accounted for. 5 minute per day drift backward for Rasberry Pi without RTC chip
function iscachetimerup($xmlfile)
    {
        $cachexml = simplexml_load_file($xmlfile);
        //$cachecurrent = $cachexml[currentTime];
        $cacheuntil = $cachexml->cachedUntil;
        date_default_timezone_set('UTC');
        $cachetimediff = timedifference(date('Y-m-d G:i:s'),$cacheuntil);
        if ($cachetimediff < 0)
            {
                return TRUE;
            }
        else
            {
                echo "<br>Time till cache expires: " . $cachetimediff . " seconds\n\n";
                return FALSE;
            }
    }
    
function timedifference ($firsttime, $lasttime)
    {
        $firsttime = strtotime($firsttime);
        $lasttime = strtotime($lasttime);
        $timedifference = $lasttime-$firsttime;
        return $timedifference;
    }
function ticketgen ($ticketmaxgen)
    {
        $randticketgen = rand(1,$ticketmaxgen);
        return $randticketgen;
    }
?>