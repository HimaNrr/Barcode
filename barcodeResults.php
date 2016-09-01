<?php
	//schema - obieewk, environment - $_SESSION['environment']
        include($_SERVER['DOCUMENT_ROOT'].'/dbinclude/lms.php');
        include($_SERVER['DOCUMENT_ROOT'].'/dbinclude/dbconnect.php');
		
	if (!$conn) {
    	$e = oci_error();
    	trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
	}else{
		if($_GET["txtKeyword"] != ""){ 
			$strSQL = "SELECT  initcap(barcode_name) as barcode_name,description,barcode_type_description
			   from letter_barcode a, letter_barcode_type b
			   WHERE (a.barcode_type_id = b.barcode_type_id) and (upper(BARCODE_NAME) LIKE upper('%".$_GET["txtKeyword"]."%'))
			   order by initcap(barcode_name)";

              	$className="filtered";
		}else{
			$strSQL = "select initcap(barcode_name) as barcode_name,description,barcode_type_description
                        from letter_barcode a, letter_barcode_type b
				where (a.barcode_type_id = b.barcode_type_id)
				order by initcap(barcode_name)";
			
			$className="all";
		}
	
			
		$objParse = oci_parse($conn, $strSQL);
		oci_execute ($objParse,OCI_DEFAULT);

		$Num_Rows = oci_fetch_all($objParse, $Result);
		include('/usr/local/apache2/htdocs-ssl/dbinclude/dbclose.php');

		$Per_Page = 5;   
		$Page = $_GET["Page"];
		if(!$_GET["Page"]){
			$Page=1;
		}

		$Prev_Page = $Page-1;
		$Next_Page = $Page+1;

		$Page_Start = (($Per_Page*$Page)-$Per_Page);
		if($Num_Rows<=$Per_Page){
			$Num_Pages =1;
		}else if(($Num_Rows % $Per_Page)==0){
			$Num_Pages =($Num_Rows/$Per_Page) ;
		}else{
			$Num_Pages =($Num_Rows/$Per_Page)+1;
			$Num_Pages = (int)$Num_Pages;
		}

		$Page_End = $Per_Page * $Page;
		if ($Page_End > $Num_Rows){
			$Page_End = $Num_Rows;
		}?>
		<form id='editBarcodes' method='post' class=<?=$className;?> >
			<table id='resultstable' class='table-barcodes' >
      				<thead>
          				<tr>
             				<th>Barcode Name</th>
						    <th>Description</th>
	      					<th>Barcode Type</th>  
	      				</tr>
      				</thead>
      				<tbody>
	
       		<?$foundme = "FALSE";
			for($i=$Page_Start;$i<$Page_End;$i++){
        			$foundme = "TRUE";
			?>
	  				<tr class="clickable-row" >
						<td class="barcode-name"><?=$Result["BARCODE_NAME"][$i];?></td>
						<td class="description"><?=$Result["DESCRIPTION"][$i];?></td>
						<td class="barcode-x"><?=$Result["BARCODE_TYPE_DESCRIPTION"][$i];?></td>
					</tr>
			<?}
     
       		if($foundme=="FALSE"){?>
					<tr>       				
						<td colspan="5"><center>No Data Found</center></td>
					</tr>
       		<?}?>
        
				</tbody>
			</table> 
        
		</form>
			<br>
			Total <?= $Num_Rows;?> Records : <?=$Num_Pages;?> Pages :
			<?php if($Prev_Page){
			echo " <a href='$_SERVER[SCRIPT_NAME]?Page=$Prev_Page&txtKeyword=$_GET[txtKeyword]'>Back&nbsp;</a> ";
			}
			for($i=1; $i<=$Num_Pages; $i++){
				if($i != $Page){
					echo "<a href='$_SERVER[SCRIPT_NAME]?Page=$i&txtKeyword=$_GET[txtKeyword]'>$i</a>&nbsp;";
				}else{
					echo "<b> $i </b>";
				}
			}
			if($Page!=$Num_Pages){
				echo "<a href ='$_SERVER[SCRIPT_NAME]?Page=$Next_Page&txtKeyword=$_GET[txtKeyword]'>Next</a> ";
			}
	
	}	
?>
