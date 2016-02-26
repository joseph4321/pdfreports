<?php
require('fpdf.php');

class PDF extends FPDF
{
function Header()
{
    global $rootDir;
    $this->Image($rootDir . "/image.jpg",175,4,30);
    $this->SetFont('Arial','B',15);
    $this->Cell(80);
    $this->Ln(20);
    $this->Image($rootDir . "/watermark3.png",0,260,80);
}

function Footer()
{
    global $rootDir;
    $this->SetY(-15);
    $this->SetFont('Arial','I',8);
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}','',0,'C');
}
}

$SYSTEMNAMEX=20;
$SYSTEMNAMEY=30;
$GRAPH6X=37;
$rootDir="/root/custom_pages/zabbix_scripts";
$devices="";
$types="";
$tdevices = `ls *-systeminfo.csv`;
$adevices = explode("\n",$tdevices);
$count=0;
$dids = file_get_contents($rootDir."/properties.csv"); $dids = rtrim($dids);
for($i=0;$i<count($adevices);$i++){
        $tmp = explode("-",$adevices[$i]);
        if(count($tmp)>1){
                $devices[$count]=$tmp[0];
                $types[$count]=`head -1 $rootDir/${tmp[0]}-systeminfo.csv`;
                $count++; continue;
        }
}

$pdf = new PDF();
$pdf->SetAuthor("Company");
$pdf->SetCreator("Joseph");
$pdf->AliasNbPages();
$pdf->AddPage();
/*****************************************
*
* Title page
*
*****************************************/
$stime=`date -d "7 days ago"`;$stime=rtrim($stime);
$etime=`date`;$etime=rtrim($etime);
$pdf->SetXY(68,90);
$pdf->SetFont('Arial','B',24);
$pdf->Cell(80,15,"Networking Weekly Report",0,0,'C');
$pdf->SetXY(65,100);
$pdf->SetFont('Arial','',14);
$pdf->Cell(80,20,"(".$stime." - ".$etime.")",0,0,'C');
$pdf->AddPage();




/*****************************************
*
* Number of devices per property
*
*****************************************/

// title
$pdf->SetTextColor(0);
$pdf->SetFont('Arial','B',18);
$pdf->SetXY(40,30);
$pdf->Cell(140,12,"Number of Devices Being Monitored",'B',1,'C',false);

$sdata = explode("\n",$dids);
$numberOfDevices = array();
foreach ($sdata as $line){
	$aline = explode(",",$line);

	// count number of devices for each category
	if($aline[2]=="drc" || $aline[2]=="cc"){
		$numberOfDevices[$aline[0]]['server']++;
	}
	else if($aline[2]!="switch"){
		$numberOfDevices[$aline[0]][$aline[2]]++;
	}
	else if($aline[2]!="router"){
		$numberOfDevices[$aline[0]][$aline[2]]++;
	}
	else{
		$numberOfDevices[$aline[0]]['other']++;
	}
}
$props = array_keys($numberOfDevices);

$leftMargin=28;
$numRows = count($sdata);
$pdf->SetFillColor(192,192,192);
$pdf->SetTextColor(255);
$pdf->SetLineWidth(.3);
$pdf->SetFont('Arial','B',8);
$header = array("Property","Switches","Routers","Servers","Other","Total");
$w = array(40,20,20,20,20,40);
$pdf->SetTextColor(255);
$pdf->SetLineWidth(.3);
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(100,100,100);
$pdf->SetXY($leftMargin,60);
$pdf->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5],7,"Device Overview",1,1,'C',true);

$pdf->SetX($leftMargin);
$pdf->SetTextColor(255);
$pdf->SetLineWidth(.3);
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(192,192,192);

for($i=0;$i<count($header);$i++){
        $pdf->Cell($w[$i],7,$header[$i],1,0,'C',true);
}
$pdf->Ln();
$pdf->SetFillColor(224,224,224);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial','',8);
$fill = false;
$numberOfEvents=0;
$numd=0;
$nump=0;
for($i=0;$i<count($props);$i++){
	$total=0;
	$pdf->SetX($leftMargin);
	$pdf->SetFont('Arial','B',8);
	// col 1, property name
	$pdf->Cell($w[0],6,$props[$i],'LR',0,'L',$fill);

	$pdf->SetFont('Arial','',8);
	// col 2, number of switches
	$switches=0;
	if(array_key_exists("switch",$numberOfDevices[$props[$i]])){
		$switches = $numberOfDevices[$props[$i]]['switch'];
	}else{$switches=0;}
	$pdf->Cell($w[1],6,$switches,'LR',0,'C',$fill);
	$numd += $switches;
	$total += $switches;
		
	// col 3, number of routers
	$routers=0;
        if(array_key_exists("router",$numberOfDevices[$props[$i]])){
		$routers = $numberOfDevices[$props[$i]]['router'];
	}else{$routers=0;}
	$pdf->Cell($w[2],6,$routers,'LR',0,'C',$fill);
	$numd += $routers;
	$total += $routers;

	// col 4, number of servers
	$servers=0;
        if(array_key_exists("server",$numberOfDevices[$props[$i]])){
		$servers += $numberOfDevices[$props[$i]]['server'];
	}else{$servers=0;}
	$pdf->Cell($w[3],6,$servers,'LR',0,'C',$fill);
	$numd += $servers;
	$total += $servers;

	// col 5, other
	$other=0;
        if(array_key_exists("other",$numberOfDevices[$props[$i]])){
		$other += $numberOfDevices[$props[$i]]['other'];
	}else{$other=0;}
	$pdf->Cell($w[4],6,$other,'LR',0,'C',$fill);
	$numd += $other;
	$total += $other;

	$pdf->Cell($w[5],6,$total,'LR',0,'C',$fill);

        $pdf->Ln();
        $fill = !$fill;
	$nump++;
}
$pdf->SetX($leftMargin);
$pdf->Cell($w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5],6,$numd." devices being monitored in ".$nump." properties.",'TBLR',0,'L',false);
$pdf->AddPage();















/*****************************************
*
* Events
*
*****************************************/
// title
$pdf->SetTextColor(0);
$pdf->SetFont('Arial','B',18);
$pdf->SetXY(40,30);
$pdf->Cell(140,12,"Events",'B',1,'C',false);
$pdf->Ln();
$leftMargin=8;
$allEvents="";
$all = explode("\n",$dids);
$seenProperties="N/A";
$eventsPerProperty = array();
$eventsPerPropertyPerPriority = array();
$pri = Array(0,0,0,0,0,0);
for($i=0;$i<count($all);$i++){
        list($p,$id,$t,$dir) = explode(",",$all[$i]);
	if(array_key_exists($p,$eventsPerProperty)){}
	else{$eventsPerProperty[$p]=0;}
	if(array_key_exists($p,$eventsPerPropertyPerPriority)){}
	else{
		for($j=0;$j<6;$j++){
			$eventsPerPropertyPerPriority[$p][$j]=0;
		}
	}
	$raw = file_get_contents($rootDir . "/".$dir."/".$id."-events.csv");$raw=rtrim($raw);
	$araw = explode("\n",$raw);
	// number of events by priority
	for($j=0;$j<count($araw);$j++){
        	if(strlen($araw[$j])<2){continue;}
        	$row = explode(",",$araw[$j]);
		if(count($row)!=4){$araw[$j].= ",0";$pri[0]++;continue;}
        	$pri[$row[3]]++;
		$eventsPerProperty[$p]++;
		$eventsPerPropertyPerPriority[$p][$row[3]]++;
	}
	for($j=0;$j<count($araw);$j++){
		if(strlen($araw[$j])<2){continue;}
		list($tmpd,$tmpe,$tmpdate,$tmpp) = explode(",",$araw[$j]);
		$d = `date +%s -d "$tmpdate"`;$d=rtrim($d);
		$allEvents .= $tmpd.",".$tmpe.",".$d .",".$tmpp.",".$p.",".$id.",".$t.",".$dir."\n";
	}
}


$pdf->SetFillColor(192,192,192);
$pdf->SetTextColor(255);
$pdf->SetLineWidth(.3);
$pdf->SetFont('Arial','B',8);
$header = array("Property","Priority","Total");
$w = array(34,144,18);
$pdf->SetTextColor(255);
$pdf->SetLineWidth(.3);
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(100,100,100);
$pdf->SetX($leftMargin);
$pdf->Cell(196,7,"Events Per Property",1,1,'C',true);

$pdf->SetTextColor(255);
$pdf->SetLineWidth(.2);
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(192,192,192);

$pdf->SetX($leftMargin);
$pdf->Cell($w[0],7,$header[0],1,0,'C',true);
$pdf->SetFont('Arial','B',8);
$pdf->SetTextColor(255,255,255);
$pdf->SetFillColor(219,219,219); $pdf->Cell(24,7,"Not Classified",1,0,'C',true);
$pdf->SetFillColor(214,246,255); $pdf->Cell(24,7,"Informational",1,0,'C',true);
$pdf->SetFillColor(255,246,165); $pdf->Cell(24,7,"Warning",1,0,'C',true);
$pdf->SetFillColor(255,182,137); $pdf->Cell(24,7,"Average",1,0,'C',true);
$pdf->SetFillColor(255,153,153); $pdf->Cell(24,7,"High",1,0,'C',true);
$pdf->SetFillColor(255,56,56); $pdf->Cell(24,7,"Disaster",1,0,'C',true);
$pdf->SetFillColor(192,192,192);
$pdf->SetTextColor(255);
$pdf->SetFont('Arial','B',10);
$pdf->Cell($w[2],7,$header[2],1,0,'C',true);

$pdf->Ln();
$pdf->SetFillColor(224,224,224);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial','',7);
$fill = false;

$numberOfEvents=0;
$props = array_keys($eventsPerProperty);
$count=0;
$pdf->SetFont('Arial','',7);
$pdf->SetFillColor(224,224,224);
$pdf->SetLineWidth(.3);
for($i=0;$i<count($props);$i++){
        $pdf->SetX($leftMargin);
        $border='LR';
	$pdf->SetFillColor(224,224,224);
        $pdf->Cell($w[0],6,$props[$i],$border,0,'L',$fill);
        for($j=0;$j<6;$j++){
                if($j == 0){$pdf->SetFillColor(219,219,219);}
                if($j == 1){$pdf->SetFillColor(214,246,255);}
                if($j == 2){$pdf->SetFillColor(255,246,165);}
                if($j == 3){$pdf->SetFillColor(255,182,137);}
                if($j == 4){$pdf->SetFillColor(255,153,153);}
                if($j == 5){$pdf->SetFillColor(255,56,56);}
                $pdf->Cell(24,6,$eventsPerPropertyPerPriority[$props[$i]][$j],$border,0,'C',true);
                $numberOfEvents+=$eventsPerPropertyPerPriority[$props[$i]][$j];
        }
	$pdf->SetFillColor(224,224,224);
        $pdf->Cell($w[2],6,$eventsPerProperty[$props[$i]],$border,0,'C',$fill);
        $pdf->Ln();
        $fill = !$fill;
        $count++;
}
$pdf->SetX($leftMargin);
$pdf->SetFont('Arial','B',8);
$pdf->Cell($w[0],6,"Total",'TLRB',0,'L',false);
for($i=0;$i<6;$i++){
        if($i == 0){$pdf->SetFillColor(219,219,219);}
        if($i == 1){$pdf->SetFillColor(214,246,255);}
        if($i == 2){$pdf->SetFillColor(255,246,165);}
        if($i == 3){$pdf->SetFillColor(255,182,137);}
        if($i == 4){$pdf->SetFillColor(255,153,153);}
        if($i == 5){$pdf->SetFillColor(255,56,56);}
        $pdf->Cell(24,6,$pri[$i],1,0,'C',false);
}
$pdf->Cell($w[2],6,$numberOfEvents,'TLRB',0,'C',false);
$pdf->Ln();
$pdf->Ln();






$pdf->SetTextColor(0);
$pdf->SetFont('Arial','B',18);
$pdf->SetX($leftMargin);

$pdf->SetFillColor(192,192,192);
$pdf->SetTextColor(255);
$pdf->SetLineWidth(.3);
$pdf->SetFont('Arial','B',8);
$header = array("Date","Property","Pri","Event");
$w = array(26,30,7,133);
$pdf->SetTextColor(255);
$pdf->SetLineWidth(.3);
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(100,100,100);
$pdf->SetX($leftMargin);
$pdf->Cell($w[0]+$w[1]+$w[2]+$w[3],7,"Event Table",1,1,'C',true);

$pdf->SetX($leftMargin);
$pdf->SetTextColor(255);
$pdf->SetLineWidth(.2);
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(192,192,192);

for($i=0;$i<count($header);$i++){
        $pdf->Cell($w[$i],7,$header[$i],1,0,'C',true);
}
$pdf->Ln();
$pdf->SetFillColor(224,224,224);
$pdf->SetTextColor(0);
$pdf->SetFont('Arial','',7);
$fill = false;
$numberOfEvents=0;

$fill = false;
$numberOfEvents=0;
$aallEvents = sortEvents($allEvents);
for($j=0; $j<count($aallEvents);$j++){
	$pdf->SetX($leftMargin);
        $row = explode(",",$aallEvents[$j]);
	if($row[2] == 102107803){continue;}
	$row[2] = `date -d \@${row[2]}`;$row[2] = rtrim($row[2]);
	$row[2] = preg_replace("/PDT.*/","",$row[2]);
        $pdf->Cell($w[0],6,$row[2],'LR',0,'L',$fill);
        $pdf->Cell($w[1],6,$row[4],'LR',0,'L',$fill);

        if(count($row)!=4 || $row[3] == 0){$pdf->SetFillColor(219,219,219);}
        if($row[3] == 1){$pdf->SetFillColor(214,246,255);}
        if($row[3] == 2){$pdf->SetFillColor(255,246,165);}
        if($row[3] == 3){$pdf->SetFillColor(255,182,137);}
        if($row[3] == 4){$pdf->SetFillColor(255,153,153);}
        if($row[3] == 5){$pdf->SetFillColor(255,56,56);}
        $pdf->Cell($w[2],6," ",'LR',0,'L',1);
        $pdf->SetFillColor(224,224,224);

        $pdf->Cell($w[3],6,$row[1],'LR',0,'L',$fill);
        $pdf->Ln();
        $fill = !$fill;
        $numberOfEvents++;
}
$pdf->Cell($w[0]+$w[1]+$w[2]+$w[3],6,"There were a total of " . $numberOfEvents . " events",'TBLR',0);

$pdf->AddPage();







/*****************************************
*
* Top 10 charts
*
*****************************************/
	
$sdata = explode("\n",$dids);
$incomingSwitchTraffic="";
$incomingSwitchTrafficTop10="";
$outgoingSwitchTraffic="";
$outgoingSwitchTrafficTop10="";
$incomingRouterTraffic="";
$outgoingRouterTraffic="";
$incomingServerTraffic="";
$outgoingServerTraffic="";
$switchPingResponse="";
$routerPingResponse="";
$serverPingResponse="";
$switchCPU="";
$routerCPU="";
$serverCPU="";
$switchPing="";
$routerPing="";
$serverPing="";
$switchMemory="";
$routerMemory="";
$serverMemory="";
foreach ($sdata as $line){
        $aline = explode(",",$line);
	if($aline[2] == "switch"){

		// incoming traffic
		$incomingSwitchTraffic="";
		$incomingTemp = file_get_contents($rootDir . "/".$aline[3]."/".$aline[1]."-traffic-incoming-lite.csv");
		$incomingTemp=rtrim($incomingTemp);
		$aincomingTemp = explode("\n",$incomingTemp);
		for($i=0;$i<count($aincomingTemp);$i++){
			$incomingSwitchTraffic .= $aincomingTemp[$i].",".$aline[0].",".$aline[1].",".$aline[2].",".$aline[3]."\n";
		}
		rtrim($incomingSwitchTraffic);
		$tmpin = getTop10($incomingSwitchTraffic);
		$in = implode("\n",$tmpin);
		$tmpin = getTop10($incomingSwitchTrafficTop10."\n".$in);
		$incomingSwitchTrafficTop10 = implode("\n",$tmpin);


		// outgoing traffic
		$outgoingSwitchTraffic="";
                $outgoingTemp = file_get_contents($rootDir . "/".$aline[3]."/".$aline[1]."-traffic-outgoing-lite.csv");
                $outgoingTemp=rtrim($outgoingTemp);
                $aoutgoingTemp = explode("\n",$outgoingTemp);
                for($i=0;$i<count($aoutgoingTemp);$i++){
                        $outgoingSwitchTraffic .= $aoutgoingTemp[$i].",".$aline[0].",".$aline[1].",".$aline[2].",".$aline[3]."\n";
                }
                rtrim($outgoingSwitchTraffic);
                $tmpout = getTop10($outgoingSwitchTraffic);
                $out = implode("\n",$tmpout);
                $tmpout = getTop10($outgoingSwitchTrafficTop10."\n".$out);
                $outgoingSwitchTrafficTop10 = implode("\n",$tmpout);
		
		
		// cpu
		$cpuTemp = file_get_contents($rootDir . "/".$aline[3]."/".$aline[1]."-traffic-other-lite.csv");
		$cpuTemp = rtrim($cpuTemp);
		$acpuTemp = explode("\n",$cpuTemp);
                for($i=0;$i<count($acpuTemp);$i++){
			if(preg_match("/CPU Usage/",$acpuTemp[$i])){
                        	$switchCPU .= $acpuTemp[$i].",".$aline[0].",".$aline[1].",".$aline[2].",".$aline[3]."\n";
			}
		}
		// memory
		$memTemp = file_get_contents($rootDir . "/".$aline[3]."/".$aline[1]."-traffic-other-lite.csv");
                $memTemp = rtrim($memTemp);
                $amemTemp = explode("\n",$memTemp);
		$memFreeTemp=0;
		$memUsedTemp=0;
		$done=0;
                for($i=0;$i<count($amemTemp);$i++){
                        if(preg_match("/Memory Free/",$amemTemp[$i])){
				list($tmpv,$tmpd) = explode(",",$amemTemp[$i]);
				$memFreeTemp=$tmpv;
				continue;
                        }
			else if(preg_match("/Memory Used/",$amemTemp[$i])){
				list($tmpv,$tmpd) = explode(",",$amemTemp[$i]);
                                $memUsedTemp=$tmpv;
				continue;
                        }
			if($memFreeTemp!=0 && $memUsedTemp!=0 && $done==0){
				$switchMemory .= round(($memUsedTemp/($memFreeTemp+$memUsedTemp))*100,2).",Memory,".$aline[0].",".$aline[1].",".$aline[2].",".$aline[3]."\n";
				$done++;
			}
                }
	
	
		// ping
                $pingTemp = file_get_contents($rootDir . "/".$aline[3]."/".$aline[1]."-traffic-other-lite.csv");
                $pingTemp = rtrim($pingTemp);
                $apingTemp = explode("\n",$pingTemp);
                for($i=0;$i<count($apingTemp);$i++){
                        if(preg_match("/Ping/",$apingTemp[$i])){
                                $switchPing .= $apingTemp[$i].",".$aline[0].",".$aline[1].",".$aline[2].",".$aline[3]."\n";
                        }
                }
		
	}
	else if($line[2] == "drc"){}
}

$tmpcpu = getTop10($switchCPU);
$switchCPU = implode("\n",$tmpcpu);

$tmpping = getTop10($switchPing);
$switchPing = implode("\n",$tmpping);

$tmpmem = getTop10($switchMemory);
$switchMemory = implode("\n",$tmpmem);

// title
$pdf->SetTextColor(0);
$pdf->SetFont('Arial','B',18);
$pdf->SetXY(40,30);
$pdf->Cell(140,12,"Top 10 Summary Charts",'B',1,'C',false);
$pdf->Ln();
$pdf->Image($rootDir . "/pdf-processor1.jpg",15,55,28,25);
printCPU("CPU Usage",$switchCPU,48,"%");
$pdf->SetY(150);
$pdf->Image($rootDir . "/pdf-memory.jpg",168,147,38,38,"JPEG");
printCPU("Process Memory Usage",$switchMemory,20,"%");
$pdf->AddPage();

$pdf->SetXY(20,50);
$pdf->Image($rootDir . "/pdf-bandwidth-download.png",10,50,18,30,"PNG");
printBandwidthValues("Incoming Traffic",$incomingSwitchTrafficTop10,34);
$pdf->SetXY(20,150);
$pdf->Image($rootDir . "/pdf-bandwidth-upload.png",182,150,18,30,"PNG");
printBandwidthValues("Outgoing Traffic",$outgoingSwitchTrafficTop10,17);
$pdf->AddPage();

$pdf->SetXY(20,50);
$pdf->Image($rootDir . "/pdf-ping1.gif",15,50,20,20,"GIF");
printCPU("Ping Response Time",$switchPing,44," ms");

$pdf->AddPage();

$pdf->Output();











/********************************
*
* Functions
*
********************************/




function getTop10($a){
	$aincoming = explode("\n",$a);
	$incomingTop10Values = Array();
	$incomingTop10Descriptions = Array();
	for($i=0;$i<10;$i++){$incomingTop10Values[$i]="0,N/A,N/A,N/A,N/A,N/A";}
	for($i=0;$i<10;$i++){$incomingTop10Descriptions[$i]="N/A";}
	for($i=0;$i<count($aincoming);$i++){
		if(strlen($aincoming[$i])<2){continue;}
		list($v,$d,$p,$id,$t,$dir) = explode(",",$aincoming[$i]);
		for($j=count($incomingTop10Values)-1;$j>=0;$j--){
			list($tmpv,$tmpd,$tmpp,$tmpid,$tmpt,$tmpdir) = explode(",",$incomingTop10Values[$j]);
			if($v >= $tmpv){
				if($j==9){
					$incomingTop10Values[$j]=$aincoming[$i];
				}
				else{
					$tmpvalue = $incomingTop10Values[$j];
					$incomingTop10Values[$j] = $aincoming[$i];
					$incomingTop10Values[$j+1] = $tmpvalue;
				}
			}
		}
	}
	return $incomingTop10Values;
}

function sortEvents($a){
    $aincoming = explode("\n",$a);
    $incomingTop10Values = Array();
    $incomingTop10Descriptions = Array();
    for($i=0;$i<count($aincoming);$i++){$incomingTop10Values[$i]="0,N/A,102107803,N/A,N/A,N/A";}
    for($i=0;$i<count($aincoming);$i++){$incomingTop10Descriptions[$i]="N/A";}
    for($i=0;$i<count($aincoming);$i++){
	    if(strlen($aincoming[$i])<2){continue;}
            list($d,$e,$date,$pri,$prop,$id,$t,$dir) = explode(",",$aincoming[$i]);
            for($j=count($incomingTop10Values)-1;$j>=0;$j--){
		    if(strlen($incomingTop10Values[$j])<4){continue;}
                    list($tmpd,$tmpe,$tmpdate,$tmpprop,$tmpid,$tmpt) = explode(",",$incomingTop10Values[$j]);
                    if($date >= $tmpdate){
                            if($j==count($incomingTop10Values)-1){
                                    $incomingTop10Values[$j]=$aincoming[$i];
                            }
                            else{
                                    $tmpvalue = $incomingTop10Values[$j];
                                    $incomingTop10Values[$j] = $aincoming[$i];
                                    $incomingTop10Values[$j+1] = $tmpvalue;
                            }
                    }
            }
    }
    return $incomingTop10Values;
}

function printBandwidthValues($s,$v,$m){
	global $pdf,$rootDir;
	$pdf->SetTextColor(255);
	$pdf->SetLineWidth(.3);
	$pdf->SetFont('Arial','B',10);
	$header = array("Property","Interface","Value");
	$w = array(40,80,40);
	$pdf->SetFillColor(100,100,100);
	$pdf->SetX($m);
	$pdf->Cell($w[0]+$w[1]+$w[2],7,$s,1,0,'C',true);
	$pdf->Ln();
	$pdf->SetFillColor(192,192,192);
	$pdf->SetX($m);
	for($i=0;$i<count($header);$i++){
		$pdf->Cell($w[$i],7,$header[$i],1,0,'C',true);
	}
	$pdf->Ln();
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0);
	$pdf->SetFont('Arial','',7);
	$fill = false;
	$numberOfEvents=0;
	$av = explode("\n",$v);
	for($j=0; $j<count($av);$j++){
		$pdf->SetX($m);
		list($iv,$d,$p,$id,$t,$dir) = explode(",",$av[$j]);
		$device = `cat $rootDir/$dir/$id-systeminfo.csv|grep sysName|awk -F"," '{print $3}'`;
		$device = rtrim($device);
		$mbps = $iv/(1024);
		$mbps1 = $mbps/(1024);
	
		$pdf->SetFont('Arial','B',7);
		$pdf->Cell($w[0],6,$p,'LRB',0,'L',$fill);
		$pdf->SetFont('Arial','',7);
		if($j==9){
			$pdf->Cell($w[1],6,$device."-".$d,'LRB',0,'L',$fill);
		}
		else{
			$pdf->Cell($w[1],6,$device."-".$d,'LRB',0,'L',$fill);
		}
		$value=0;
		if($mbps < 1){
			$value=round($iv,2)." bps";
		}
		else if($mbps1 < 1){
			$value=round($mbps,2)." kbps";
		}
		else{
			$value=round($mbps1,2)." Mbps";
		}
		if($j==9){
			$pdf->Cell($w[2],6,$value,'LRB',0,'L',$fill);
		}
		else{
			$pdf->Cell($w[2],6,$value,'LRB',0,'L',$fill);
		}
		$pdf->Ln();
		$fill = !$fill;
		$numberOfEvents++;
	}
}


function printCPU($s,$v,$m,$u){
	global $pdf,$rootDir;
	$pdf->SetTextColor(255);
	$pdf->SetLineWidth(.3);
	$pdf->SetFont('Arial','B',10);
	$header = array("Property","Device","Value");
	$w = array(40,65,40);
	$pdf->SetFillColor(100,100,100);
	$pdf->SetX($m);
	$pdf->Cell($w[0]+$w[1]+$w[2],7,$s,1,0,'C',true);
	$pdf->Ln();
	$pdf->SetFillColor(192,192,192);
	$pdf->SetX($m);
	for($i=0;$i<count($header);$i++){
		$pdf->Cell($w[$i],7,$header[$i],1,0,'C',true);
	}
	$pdf->Ln();
	$pdf->SetFillColor(224,224,224);
	$pdf->SetTextColor(0);
	$pdf->SetFont('Arial','',7);
	$fill = false;
	$numberOfEvents=0;
	$av = explode("\n",$v);
	for($j=0; $j<count($av);$j++){
		$pdf->SetX($m);
		list($iv,$d,$p,$id,$t,$dir) = explode(",",$av[$j]);
		$device = "N/A";
		if($d != "N/A"){
		$device = `cat $rootDir/$dir/$id-systeminfo.csv|grep sysName|awk -F"," '{print $3}'`;
		$device = rtrim($device);
		}

		$pdf->SetFont('Arial','B',7);
		$pdf->Cell($w[0],6,$p,'LRB',0,'L',$fill);
		$pdf->SetFont('Arial','',7);
		$pdf->Cell($w[1],6,$device,'LRB',0,'L',$fill);
		$pdf->Cell($w[2],6,round($iv,2).$u,'LRB',0,'L',$fill);

		$pdf->Ln();
		$fill = !$fill;
		$numberOfEvents++;
	}
}


?>