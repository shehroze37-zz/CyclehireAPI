<?php

	//scp api.php root@ubuntu1.tombrown.it:/var/www/html/api.php 
	//password ubuntu1
	date_default_timezone_set('Europe/London');
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
	
	
	header('Access-Control-Allow-Origin:*') ;
	
	
	if(isset($_POST['hours']) && isset($_POST['minutes'])){
		
		$hours = $_POST['hours'] ;
		$minutes = $_POST['minutes'] ;
		
		
		$now = strtotime("now"); // current timestamp
		$hour_later_main = strtotime("+" .$hours ." hour " .$minutes ." minutes"); // hour later
		$now = date("Y-m-d H:i:s", $now);
		$hour_later = date("Y-m-d H:i", $hour_later_main);
		
		
		$predicted_minutes = date("i", $hour_later_main);
		$predicted_hours = date("H", $hour_later_main);
		
		//echo "Predicted minutes are " . $predicted_minutes . "<br>" ;
		
		if(floor($predicted_minutes / 30) != 0){
			$predicted_hours = $predicted_hours + floor(($predicted_minutes / 30)) ;
		}
		
		$predicted_minutes = $predicted_minutes % 30 ;
		
		//echo "Predicted minutes now are " . $predicted_minutes . "<br>" ;
		
		
		
		if($predicted_minutes < 15){
			$predicted_minutes = "00" ;
		}
		else if($predicted_minutes > 15 && $predicted_minutes < 30){
			$predicted_minutes = "30" ;
			
		}
		else if($predicted_minutes > 30 && $predicted_minutes < 45){
			$predicted_minutes = "30" ;
		}
		else if($predicted_minutes > 45){
			$predicted_minutes == "00" ;
			$predicted_hours = $predicted_hours + 1 ;
		}
		
		//echo "Predicted minutes now are " . $predicted_minutes . "<br>" ;
		
		$predicted_date = date("Y-m-d", $hour_later_main) . " " . $predicted_hours . ":" . $predicted_minutes . ":00";
		
		$dbc =  mysqli_connect("localhost","root","cyclehire","cyclehire") or die("Error " . mysqli_error($link));
		 
		//echo $predicted_date ;
		//exit() ; 
		 
		$query = "SELECT DISTINCT p.stationId, b.longitude, bs.locked, bs.installed, b.latitude, b.name, p.predictedBikesAvailable, p.predictedTime, p.avgGrow, p.growCount  FROM predictedState AS p INNER JOIN bikeStation AS b USING(stationId) INNER JOIN bikeStationState AS bs USING(stationId) WHERE predictedTime = '$predicted_date' ORDER BY predictedBikesAvailable ASC" or die("Error in the consult.." . mysqli_error($dbc)); 
	
		$result = $dbc->query($query);
		
		header('HTTP/1.0 200 OK');
		$output = array() ;
		while($row = mysqli_fetch_array($result)) { 
		  
		  $nested_array = array() ;
		  
		  foreach ($row as $k => $v)
			{
				if(!is_numeric($k)){
				
					$nested_array[$k] = $v ;
				}
			}
			
			if($row['locked'] == 1 || $row['locked'] == "1"){
				$nested_array['error_code'] = "1" ;
				$nested_array['error_description'] = "Bike Station is locked down" ;
			}
			else{
				
				$nested_array['error_code'] = "0" ;
				$nested_array['error_description'] = NULL ;
				
			}
		  
		  $output[] = $nested_array ;
		   
		} 
		
		
		
		echo json_encode($output) ; 
		exit() ;
		
	}
	else{
		echo "Error : No hours and minutes supplied for prediction" ;
		exit() ;
	}

?>