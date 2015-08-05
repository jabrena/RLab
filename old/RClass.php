<?php
class R{

	//Configuration Variables
	var $temp_dir;
	var $R_path;
	var $R_options_1;
	var $R_options_2;
	var $graphic;
	var $bannedCommandConfigFile;
	var $RCODE;
	var $Rerror;

	//Constructor de clase
	function __construct(){
		$this -> temp_dir = "tmp";
		$this -> R_path = "C:/Program Files/R/R-3.2.0/bin/x64/Rterm.exe";
		$this -> R_options_1 = "--quiet --no-restore --no-save  < ";
		$this -> R_options_2 = " > ";
		$this -> graphic = "jpeg";
		$this -> bannedCommandConfigFile = "RClass.1.0\security.txt";
	}

	//Metodo para establecer valores en propiedades
	function setProp($PropName, $PropValue){
		$this->$PropName = $PropValue;
	}

	//Funcion que comprueba el fichero de configuracion
	function checkBCCFile(){
		
		$lines = file($this -> bannedCommandConfigFile);
		//$lines = file("security.txt");
		$total = count($lines);

		$j = 0;
		for ($i=0;$i < $total;$i++){
  			$line = trim($lines[$i]);

 			if (!strrchr($line,"#")){
    			$j = $j + 1;
				if (strrchr($line,"|")){
      				$terms = explode("|",$line);
      				$bad_0 = trim($terms[0]);
      				$bad_op = trim($terms[1]);
					$bad_cmd[$j]= $bad_0;
					$bad_option[$bad_0] = $bad_op;
				}else{
					$bad_cmd[$j]= $line;
  				}
 			}else{
    			continue;
 			}
		}
		
		echo("Comprobando el fichero de configuracion<br />");
	}//End Function
	
	//Funcion que comprueba cada linea del codigo R dado, por si emplea un  commando prohibido.
	function check_bad($text,$j){
		global $bad_cmd,$bad_option;
		$is_bad = 0;
 
		foreach($bad_cmd as $bad){
			$bad1 = str_replace(".","\.",$bad);

			if(preg_match('/'.$bad1.'/',$text)){
      			if(strrchr($bad,".") && (strlen($bad) > 3)){
        			$is_bad = 1;
      			}else{
			        // get remaining string after targer key word
			        $terms = explode($bad,$text);
			        // get rid of spaces before a possible following "("
			        $term1 = ltrim($terms[1]);
			
			        if($bad_option[$bad] != ""){
						if(preg_match('/'.$bad_option[$bad].'/',$term1)){
							// if (strstr($term1,$bad_option[$bad]))
			             	$is_bad = 1;
						}
			        }else{
						if(substr($term1,0,1) == "("){
							$is_bad = 1;
						}
			        }
      			}

				if($is_bad == 1){
					if ($bad_option[$bad] != ""){
						echo "<font color=red>$bad</font> function";
						echo " with <font color=red>".$bad_option[$bad]."</font>";
						echo " option is NOT permitted ";
		         	}else{
						echo "<font color=red>$bad</font> function is NOT permitted ";
						echo "in Line <font color=red>$j</font><BR>";
						echo "<blockquote>$text</blockquote>";
						echo "Program Stopped!<BR>";
						exit;
					}
				}
		 	}
		}
	}//End function

	//Funcion que genera un nombre aleatorio a ficheros
	function random_str($size){ 
		$randoms = array( 
                0, 1, 2, 3, 4, 5, 6, 7, 8, 9, a, b, 
                c, d, e, f, g, h, i, j, k, l, m, n, 
                o, p, q, r, s, t, u, v, w, x, y, z 
		); 

		srand ((double) microtime() * 1000000); 

		for($i = 1; $i <= $size; $i++){
			$text = $randoms[(rand(0,35))];//cambio el $text . por lo que hay.
        } 

		return $text; 
	}//End function

	//Funcion que obtiene un nombre de fichero
	function get_file_name($text){
		// Unix   : bitmap(file="something.jpg")
		// Windows: jpeg(file="something.jpg")

		$temp1 = explode("file=\"",$text);
		$fname = explode("\"",$temp1[1]);

		return $fname[0];
	}//End function


	function createRInputFile($r_code){
	
		//Obtiene informacion sobre el codigo a ejecutar y lo parte en lineas
		$old_code = explode(chr(10),$r_code);
		$total = count($old_code);
		$new_code = "";
	
		for($i=0;$i < $total; $i++){
			// replace original graphic file name with a random name
			// Windows system if (ereg("jpeg",$old_code[$i]))

			// if (ereg("bitmap",$old_code[$i]))

			$j = $i+1;
			$old = $old_code[$i];
			//echo("Linea ".$i."codigo ".$old."<br />");
			
			//Mejorar esto
			//Comprueba si emplea un codigo incorrecto.
			//$this-> check_bad($old,$j);

			if(preg_match('/"jpeg"/',$old_code[$i])){
			 	$gfile_name = $this-> get_file_name($old_code[$i]);
			 	$gfile_name = $this-> random_str(4).$gfile_name;
			 
			 	//$new_code .= "bitmap(file=\"$temp_dir/$gfile_name\") \n";
				//$new_code .= "#ff;";
				
				$tmp = $this ->temp_dir;
				$new_code .= $this ->graphic."(file=\"tmp/$gfile_name\") \n";
			}else{
				$new_code .= $old_code[$i]."\n";
			}
		}
	
		//echo($new_code);
	
		$this -> r_name = $this-> random_str(10);
		$this -> r_input = $this-> temp_dir."/".$this -> r_name.".R";
		$this -> r_output = $this-> temp_dir."/".$this -> r_name.".Rout";
	
		$fp = fopen($this -> r_input,"w");
		fwrite($fp,$new_code);
		fclose($fp);
		
		echo("Fichero R generado <br />");
}

function execution(){

	try{
		// $rsoft = "/usr/local/lib/R/bin/R";
		$rsoft = $this-> R_path;
	
		// Unix :
		//    R BATCH --slave --no-save $r_input $r_output
		//
		// Windows :
		//    Rterm.exe --quiet --no-restore --no-save < test.R > test.Rout
		
		// $command = "$rsoft BATCH --slave --no-save $r_input $r_output";

		$command = $this-> R_path." ".$this -> R_options_1." ".$this -> r_input." ".$this -> R_options_2." ".$this -> r_output;
		//echo($command);
		
		$result = "";
		$error = "";
	
		$exec_result = exec($command,$result,$error);
		
		echo("R CONSOLE COMMAND: ".$command."<br />");
		
		/*
		if($error){
			$this -> Rerror = true;
		}
		*/
	
	}catch(Exception $e){
		echo "ERROR ". $e -> getCode() .
		"en la linea:" . $e -> getLine() .
		":" . $e-> getMessage();
		
		$this -> Rerror = true;
	}
}

function showResultsRCode(){
	$lines = file($this -> r_output);
$total = count($lines);

if ($this -> Rerror){
  echo "<font color=red>Error: Something wrong! Please check output!</font>";
  echo "<HR>Output of R program : <P><HR>";

	for ($i=0;$i < $total;$i++){
    	echo $lines[$i]."<BR>";
		exit;
	}
}

echo "Output of R program<HR>";

	$to_do_plot = 0;
	
	for ($i=0;$i < $total;$i++){

		$line = $lines[$i];
	
		// Unix   : if (ereg("bitmap",$line))
		// Windows: if (ereg("jpeg",$line))
	
		if (preg_match('/"jpeg"/',$line)){
	    	echo $line."<BR>";
	
			$gfile_name = $this -> get_file_name($line);
			$to_do_plot = 1;
			//echo "<P><IMG SRC=\"$file_name\"><P>";
		}else if (preg_match('/"dev.off"/',$line)){
			echo $line."<BR>";
	
			if ($to_do_plot == 1){ 
				echo "<P><IMG SRC=\"$gfile_name\"><P>";
				$to_do_plot = 0;
		    }
		}else if (preg_match('/"null device"/',$line)){
	    	continue;
		}else if (preg_match('/"          1"/',$line)){
	    	continue;
		}else{
	    	echo $line."<BR>";
		}
	}
 }//End function
 
function executeRCode($r_code){
	
	echo("SOURCE CODE: ".$r_code."<br />");
	
	$this -> checkBCCFile();
	$this -> createRInputFile($r_code);
	$this -> execution();
	$this -> showResultsRCode();
}//End function

}//End Class


?>
