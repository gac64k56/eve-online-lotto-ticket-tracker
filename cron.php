<?php
//DEV: need to fix CLI inputs and only run from CLI. Also later, get it ready for IIS. Prep win 2k3 and 2k8 VMs.
$cronargument = $argv[1];
//$cronshowids = $argv[2];
//$cronverbose = $argv[3];
if (strtoupper($cronargument) == "HELP")
    {
        die ("php -f cron.php [FORCE]");
    }
//How long did this take?
        $rendertime = microtime();
        $rendertime = explode(' ',$rendertime);
        $rendertime = $rendertime[1] + $rendertime[0];
        $rendertimestart = $rendertime;
$forceupdate = FALSE;
$showids = FALSE;
$verbose = FALSE;
if (strtoupper($cronargument) == "FORCE")
    {
        $forceupdate = TRUE;
    }
/*if (strtoupper($cronshowids) == "SHOWIDS")
    {
        $showids = TRUE;
    }
if (strtoupper($cronverbose) == "VERBOSE")
    {
        $verbose = TRUE;
    }
 * 
 */
//include database and API info to put the data into
include('config.php');
include('lottofunctions.php');



//Get XML and load it into memory
$walletxml=getxml("http://api.eveonline.com/corp/WalletJournal.xml.aspx?keyID=". $keyID ."&characterID=". $characterID ."&vcode=". $vCode);

//does the xml still exist or did something go wrong?
if (file_exists($walletxml))
    {
        //if it did...HEY let's read the XML :D
        $xml = simplexml_load_file($walletxml);
//        print_r($xml); //use to read what we're getting from the API
    }
else
    {
        //Oops, something happened.
        die('Failed to open ' . $walletxml);
    }

//Break down XML into an array to be pase into the database.
//number of rows in the current XML;
$walletrows = $xml->result->rowset->row;
$walletrowset = $xml->result->rowset;
$newitemcount=0;
$xmlheaderlist = "date, refID, refTypeID, ownerName1, ownerID1, ownerName2, ownerID2, argName1, argID1, amount, balance, reason, processed";
//Loop, break down XML
//In case CCP ever make multiple rowsets in their journal
for ($o = 0; $o < sizeof($walletrowset); $o++)
{
    //run through each 
    for ($i = 0; $i < sizeof($walletrows); $i++) //loop works, but doesn't parse XML
        {
    
            //dev; how long does this loop take?
    /*        $xmlsqltime = microtime();
            $xmlsqltime = explode(' ',$xmlsqltime);
            $xmlsqltime = $xmlsqltime[1] + $xmlsqltime[0];
            $xmlsqltimestart = $xmlsqltime;
     * 
     */        
            //date,refID,refTypeID,ownerName1,ownerID1,ownerName2,ownerID2,argName1,argID1,amount,balance,reason,processed
            $date=$walletrowset[$o]->row[$i]['date'];
            $refID=$walletrowset[$o]->row[$i]['refID'];
            $refTypeID=$walletrowset[$o]->row[$i]['refTypeID'];
            $ownerName1=$walletrowset[$o]->row[$i]['ownerName1'];
            $ownerID1=$walletrowset[$o]->row[$i]['ownerID1'];
            $ownerName2=$walletrowset[$o]->row[$i]['ownerName2'];
            $ownerID2=$walletrowset[$o]->row[$i]['ownerID2'];
            $argName1=$walletrowset[$o]->row[$i]['argName1'];
            $argID1=$walletrowset[$o]->row[$i]['argID1'];
            $amount=$walletrowset[$o]->row[$i]['amount'];
            $balance=$walletrowset[$o]->row[$i]['balance'];
            $reason=$walletrowset[$o]->row[$i]['reason'];

            //Is it in the database already?
            if(doeswalletentryexist($refID))
                {
                    //If so, skip over it
                    if($showids)
                        {
                            print "RefID: " . $refID . " is already in the database.\n";
                        }
                }
            else
                {
                    //Enter the new item into the database
                    $walletsql="INSERT INTO walletjournal (" . $xmlheaderlist . ") VALUES ('" . $date . "', '" . $refID . "', '" . $refTypeID . "', '" . $ownerName1 . "', '" . $ownerID1 . "', '" . $ownerName2 . "', '" . $ownerID2 . "', '" . $argName1 . "', '" . $argID1 . "', '" . $amount . "', '" . $balance . "', '" . $reason . "', 0)";
                    mysqli_query($con, $walletsql);
                    //echo $walletsql;
                    $newitemcount++;
                }

    /*    $xmlsqltime = microtime();
        $xmlsqltime = explode(' ', $xmlsqltime);
        $xmlsqltime = $xmlsqltime[1] + $xmlsqltime[0];
        $xmlsqltimefinish = $xmlsqltime;
        $xmlsqltimeresults = round(($xmlsqltimefinish - $xmlsqltimestart), 4);
        echo '<br>Loop ' . $i . ' was generated in '.$xmlsqltimeresults.' seconds</br>';
    * 
    */
    }
}
print ("\n");

if ($newitemcount)
    {
        print "$newitemcount entries have been processed into the database. Processing for ticket assignments.\n\n";
    }
else
    {
        //Memory Usage, DEV: remove after development
        
        if (!$forceupdate)
            {
                echo "Memory used: " . memory_get_usage(TRUE) . " Bytes\n";
                echo "Peak memory used: " . memory_get_peak_usage(TRUE) . " Bytes\n";
                echo "No new entries have been posted.\n\n";
                $rendertime = microtime();
                $rendertime = explode(' ', $rendertime);
                $rendertime = $rendertime[1] + $rendertime[0];
                $rendertimefinish = $rendertime;
                $rendertimeresults = round(($rendertimefinish - $rendertimestart), 4);
                die ("Page generated in " .$rendertimeresults. " seconds");
            }
    }


//loop; pull corp wallet from database. If reason equals one of the lotto_id's, start logic:

//Query strings for all 3 tables to parse through
$walletparsequery = mysqli_query($con, "SELECT * FROM walletjournal WHERE processed LIKE '0'");
$walletparsecount = 0;
$parsewalletrowcount = 0;
$isreasoninlottocount = 0;
$currentwalletrow = 0;
while ($parsewalletrow = mysqli_fetch_assoc($walletparsequery))
    {
        //if donation was by player, go through loop, else was it a corp?
        if ($parsewalletrow["refTypeID"] == 10)
            {
                $parsewalletrowcount++; //DEV: count how many times it went passed through this IF statement.
                $walletreasonresult = $parsewalletrow["reason"];
                //remove the DESC: from the reason
                $walletreason = str_replace("DESC: ", "", $walletreasonresult);
                //in case someone puts into the reason that it's lower or mixed case
                $walletreason = trim(strtoupper($walletreason));
                //check if it's in the lotto_id table
                $checklottoidquery = mysqli_query($con, "SELECT * FROM lotto_id");
                while ($isreasoninlottoid = mysqli_fetch_assoc($checklottoidquery))
                    {
                        $isreasoninlottoidreason = trim(strtoupper($isreasoninlottoid["reason"]));
                        //if it is, check if the date is wihin the lotto's timeframe, also put the isreasoninlottoid into upper just in case it isn't already
                        if ($isreasoninlottoidreason == $walletreason)
                            {
                                $isreasoninlottocount++;
                                $walletdate = trim($parsewalletrow["date"]);
                                $lottoidenddate = trim($isreasoninlottoid["enddate"]);
                                //Are we beyond the end date for the lotto?
                                if ($lottoidenddate > $walletdate)
                                    {
                                        //check tickets > process isk > issue tickets (if over max, stop, need winner) 
                                        $lottoticketcount = $isreasoninlottoid["max_tickets"];
                                        print ("Tickets $lottoticketcount\n"); //DEV: show ticket for current
                                        //need to match up reason with lotto_id
                                        $ticketlottoid = $isreasoninlottoid["lotto_id"];
                                        $ticketsql = "SELECT * FROM tickets WHERE lotto_id LIKE '" . $ticketlottoid . "'";
                                        $ticketquery = mysqli_query($con, "SELECT * FROM tickets WHERE lotto_id LIKE '" . $ticketlottoid . "'");
                                        //check if there is any tickets at all, be prepared for a new database or just no entries for that lotto_id
                                        //If the ticket table is empty, it is is, then the query will return false
                                        if ($ticketquery)
                                            {
                                                //How many rows are in the database for the lotto_id in question
                                                if ($lottocountrows = mysqli_prepare($con, $ticketsql))
                                                    {
                                                        mysqli_stmt_execute($lottocountrows);
                                                        mysqli_stmt_store_result($lottocountrows);
                                                        //Is the lotto full? If it is, skip over this parse.
                                                        $lottomaxtickets = $lottoticketcount - 1;
                                                        if ($lottomaxtickets >= mysqli_stmt_num_rows($lottocountrows))
                                                            {
                                                                //Time to process the isk
                                                                $lottoticketprice = trim($isreasoninlottoid["ticketprice"]);
                                                                $walletamount = trim($parsewalletrow["amount"]);
                                                                $ticketpurchased = $walletamount/$lottoticketprice;
                                                                print("Tickets Purchased $ticketpurchased\n");
                                                                //Issue ticket numbers
                                                                $i = 1;
                                                                while($i<$ticketpurchased);
                                                                    {
                                                                        print("i $i\n");
                                                                        //Create random number, give lotto players random ticket numbers to a 'better' change at winning instead of sequential numbers.
                                                                        //DEV: create settings to change from random and sequential
                                                                        $ticketnumber = ticketgen($lottoticketcount);                                                                        
                                                                        $ticketnumsql = "SELECT * FROM tickets WHERE ticketnumber = '" . $ticketnumber . "' and lotto_id = '" . $ticketlottoid . "'";
                                                                        $ticketnumquery = mysqli_query($con, $ticketnumsql);
                                                                        $ticketnumarray = mysqli_fetch_assoc($ticketnumquery);
                                                                        //If the random ticket number is in the database, keep make them till we get a new one.
                                                                        //DEV: find better way to do this, something without guessing
                                                                        print("First Ticket $ticketnumber\n");
                                                                        var_dump($ticketnumarray["ticketnumber"]);
                                                                        if($ticketnumber == $ticketnumarray["ticketnumber"])
                                                                            {
                                                                                while($ticketnumber == $ticketnumarray["ticketnumber"])
                                                                                    {
                                                                                        $ticketnumber = ticketgen($lottoticketcount);
                                                                                        print("Gen ticket $ticketnumber\n");
                                                                                    }
                                                                            }
                                                                        //Woo got our new ticket number. Now let's put it in the database for our player
                                                                        print("New Ticket $ticketnumber\n");
                                                                        $findplayerinlottosql = "INSERT INTO tickets VALUES (NULL, '" . $parsewalletrow['ownerName1'] . "'," . $parsewalletrow['ownerID1'] . "," . $lottoticketprice . ",'" . $parsewalletrow['date'] . "'," . $ticketnumber . "," . $ticketlottoid . ")";
                                                                        mysqli_query($con,$findplayerinlottosql);
                                                                        echo $findplayerinlottosql . "\n";
                                                                        $i++;
                                                                        echo "\n";
                                                                    }
                                                                //DEV: somewhere for excess / under the minimum for the admin panel. Need code to process.
                                                           }
                                                       if ($verbose)
                                                           {
                                                                print("Lotto ID $ticketlottoid is already full\n");
                                                           }
                                                        mysqli_stmt_close($lottocountrows);
                                                    }                                                
                                            }
                                        //Table is empty
                                        else
                                            {
                                                echo "Ticket table is empty.\n";
                                            }
                                    }
                            }
                    }
                
            }
        $walletparsecount++;
        $currentwalletrow++;
        //now processed = 1
    }
print ("Parsed through $walletparsecount wallet entries\n");
print ("Went through the if 10 $parsewalletrowcount times \n");
print ("Found the the reason $isreasoninlottocount times\n");
echo "\n";
/*$walletparseresult = $walletparsequery->fetch_object();

for ($walletparsecounter = 0; $walletparsecounter < mysqli_num_rows($walletparsequery); $walletparsecounter++)
    {
        echo mysqli_num_rows($walletparsequery);
        //Did a player donate the money? refTypeID 10 = Player Donation, 11 = Corp Donation
        if ($walletparseresult->refTypeID == 10)
        {
            echo "10";
            $walletreasonresult = $walletparseresult->reason;
            $walletreason = str_replace("DESC: ", "", $walletreasonresult);
            echo $walletreason;
        }
    }*/
//is already in database? refID in 'wallet' table, is processed true? if not: continue, else die

//is enddate > one inputted, continue, else die

//is payment >= ticketprice; continue, else alert lotto runner, then die

//loop, assign tickets: payment/ticketprice = tickets. Tickets++ into array, input into database

//table 'walletdata' refID processed = true

//end, repeat loop if needed

//output to log (later impliment?)

    
mysqli_close($con);
//Memory Usage, DEV: remove after development
        echo "Memory used: " . memory_get_usage(TRUE) . " Bytes\n";
        echo "Peak memory used: " . memory_get_peak_usage(TRUE) . " Bytes\n";
//So, how long did it take?
        $rendertime = microtime();
        $rendertime = explode(' ', $rendertime);
        $rendertime = $rendertime[1] + $rendertime[0];
        $rendertimefinish = $rendertime;
        $rendertimeresults = round(($rendertimefinish - $rendertimestart), 4);
        echo "Page generated in " .$rendertimeresults. " seconds\n";
?>