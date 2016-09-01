<?php	error_reporting(0);
	//schema - obieewk, environment - $_SESSION['environment']
        include($_SERVER['DOCUMENT_ROOT'].'/dbinclude/lms.php');
        include($_SERVER['DOCUMENT_ROOT'].'/dbinclude/dbconnect.php');		
	if (!$conn) {
			$e = oci_error();
			trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
	}else{
	   try{
         if(isset($_POST['create-barcode'])) {
            $sqlBarcode1 = 'INSERT INTO letter_barcode (barcode_name,description,barcode_x_coordinate, barcode_y_coordinate, barcode_rotation, barcode_height, barcode_width, text, text_x_coordinate, text_y_coordinate, text_rotation, text_font_style, text_font_size) VALUES (:barcode_name,:description,:barcode_x_coordinate,:barcode_y_coordinate,:barcode_rotation,:barcode_height,:barcode_width,:text,:text_x_coordinate,:text_y_coordinate,:text_rotation,:text_font_style,:text_font_size)';
		    $sqlBarcode2 = 'INSERT INTO letter_barcode_level_type (barcode_level_type_code) VALUES (:barcode_level_type_code)';
		    $sqlBarcode3 = 'INSERT INTO letter_barcode_page_type (barcode_page_type_cd) VALUES (:barcode_page_type_cd)';
		    $sqlBarcode4 = 'INSERT INTO letter_barcode_type (barcode_type_description) VALUES (:barcode_type_description)';
            $sqlBarcode5 = 'INSERT INTO letter_ brcode_global_link (barcode_id, global_id,barocde_order, length, pad) VALUES ()';
            }else if(isset($_POST['save-barcode'])){
            /*$sqlBarcode = 'UPDATE letter_barcode SET description=:description, barcode_x_coordinate=:barcode_x_coordinate, barcode_y_coordinate = :barcode_y_coordinate , barcode_rotation = :barcode_rotation, barcode_height = :barcode_height, barcode_width = :barcode_width, text = :text, text_x_coordinate = :text_x_coordinate, text_y_coordinate = :text_y_coordinate, text_rotation = :text_rotation, text_font_style = :text_font_style, text_font_size = :text_font_size WHERE barcode_name=:barcode_name'; 
		      $sqlBarcode = 'UPDATE letter_barcode  barcode, letter_barcode_level_type barcodelevel, letter_barcode_page_type barcodepage, letter_barcode_type barcodetype 
			    SET barcode.barcode_name=:barcode_name,
			    description=:description,
			    barcode_type_description=:barcode_type_description,
			    barcodelevel.barcode_level_type_code=:barcode_level_type_code,
			    barcodepage.barcode_page_type_cd=:barcode_page_type_cd,
                barcodetype.barcode_type_description=:barcode_type_description, 
			   WHERE (barcode.barcode_level_type_id = barcodelevel.barcode_level_type_id and
                      barcode.barcode_page_type_id = barcodepage.letter_barcode_page_type_id and
                      barcode.barcode_type_id = barcodetype.barcode_type_id and 
				      upper(barcode_name)=upper('" . $selected_barcode . "'))'; */
            }
            $sql_lbn1 = "SELECT letter_id FROM letter WHERE UPPER(letter_name) = UPPER(:letter_name)";
            $sql_lbn2 = "SELECT barcode_id from letter_barcode WHERE barcode_name=:letter_barcode";
            $sql_lbn3 = "INSERT INTO letter_barcode_link (barcode_id,letter_id) VALUES (:barcode_id,:letter_id)";
            $sql_lbnd = "DELETE FROM letter_barcode_link WHERE letter_id = :letter_id";
            $sql_lbn4 = 'UPDATE letter_barcode_link SET barcode_id=:barcode_id WHERE UPPER(letter_id) = UPPER(:letter_id)';
            $sql_DS   = "UPDATE LETTER_DEPLOY_STATUS SET BARCODE = 'Y' WHERE LETTER_ID = (SELECT letter_id FROM letter WHERE upper(letter_name) = UPPER(:letter_name_DS))";
            $objParse_lbn1 = oci_parse($conn, $sql_lbn1);
            $objParse_lbn2 = oci_parse($conn, $sql_lbn2);
            $objParse_lbnd = oci_parse($conn, $sql_lbnd);
            $objParse_lbn3 = oci_parse($conn, $sql_lbn3);
            $objParse_DS   = oci_parse($conn, $sql_DS);
            $letter_name=$_SESSION['letter'];
            oci_bind_by_name($objParse_lbn1, ':letter_name', $letter_name);
            oci_execute ($objParse_lbn1,OCI_COMMIT_ON_SUCCESS);
            oci_fetch_all($objParse_lbn1, $matchResult_lbn1);
            $letter_id = $matchResult_lbn1['LETTER_ID'][0];
            $letter_barcode = $_POST['letter_barcode'];
            if(isset($letter_barcode)){
            	oci_bind_by_name($objParse_lbnd, ':letter_id', $letter_id);
            	oci_execute ($objParse_lbnd,OCI_COMMIT_ON_SUCCESS);
            }else{throw new Exception("Adding Barcodes to Letter UnSuccessfully");}
            	
            oci_bind_by_name($objParse_lbn3, ':letter_id', $letter_id);
            foreach ($letter_barcode as $a){
            	oci_bind_by_name($objParse_lbn2, ':letter_barcode', $a);
            	oci_execute ($objParse_lbn2,OCI_COMMIT_ON_SUCCESS);
            	oci_fetch_all($objParse_lbn2, $matchResult_lbn2);
            	$barcode_id = $matchResult_lbn2['BARCODE_ID'][0];
            	oci_bind_by_name($objParse_lbn3, ':barcode_id', $barcode_id);
            	if(oci_execute ($objParse_lbn3,OCI_COMMIT_ON_SUCCESS)){
            		oci_bind_by_name($objParse_DS, ':letter_name_DS', $letter_name);
            		oci_execute ($objParse_DS,OCI_COMMIT_ON_SUCCESS);
            	}
            	else{throw new Exception("Adding Barcodes to Letter UnSuccessfully");}
            	
            }
             
            oci_free_statement($objParse_lbn1);
            oci_free_statement($objParse_lbn2);
            oci_free_statement($objParse_lbn3);
            oci_free_statement($objParse_lbnd);
            oci_free_statement($objParse_DS);
		
		$stmtBarcode1 = oci_parse($conn, $sqlBarcode1);
        $stmtBarcode2 = oci_parse($conn, $sqlBarcode2);
        $stmtBarcode3 = oci_parse($conn, $sqlBarcode3);
        $stmtBarcode4 = oci_parse($conn, $sqlBarcode4);
                
		//$letter_barcode = $_POST['letter_barcode'];
                
        $barcode_name = $_POST['barcode_name'];
        $description = $_POST['barcode_desc'];
		$barcode_x_coordinate = $_POST['barcode_x'];
        $barcode_y_coordinate = $_POST['barcode_y'];
        $barcode_rotation = $_POST['barcode_rotation'];
        $barcode_height = $_POST['barcode_height'];
        $barcode_width = $_POST['barcode_width'];
        $text = $_POST['text_barcode'];
        $text_x_coordinate = $_POST['text_x'];
        $text_y_coordinate = $_POST['text_y'];
        $text_rotation = $_POST['text_rotation'];
        $text_font_style = $_POST['text_style'];
        $text_font_size = $_POST['text_size'];
       
//		$barcode_level_type_code = $_POST['barcode_level'];
//      $barcode_page_type_cd = $_POST['barcode_appear'];
//		$barcode_type_description = $_POST['barcode_type'];
		//$mod_user  = $_SESSION['userid'];
		//oci_bind_by_name($stmtBarcode, ':letter_barcode', $letter_barcode);
                
        oci_bind_by_name($stmtBarcode1, ':barcode_name', $barcode_name);
		oci_bind_by_name($stmtBarcode1, ':description', $description);
		oci_bind_by_name($stmtBarcode1, ':barcode_x_coordinate', $barcode_x_coordinate);
        oci_bind_by_name($stmtBarcode1, ':barcode_y_coordinate', $barcode_y_coordinate);
        oci_bind_by_name($stmtBarcode1, ':barcode_rotation', $barcode_rotation);
        oci_bind_by_name($stmtBarcode1, ':barcode_height', $barcode_height);
        oci_bind_by_name($stmtBarcode1, ':barcode_width', $barcode_width);
        oci_bind_by_name($stmtBarcode1, ':text', $text);
        oci_bind_by_name($stmtBarcode1, ':text_x_coordinate', $text_x_coordinate);
        oci_bind_by_name($stmtBarcode1, ':text_y_coordinate', $text_y_coordinate);
        oci_bind_by_name($stmtBarcode1, ':text_rotation', $text_rotation);
        oci_bind_by_name($stmtBarcode1, ':text_font_style', $text_font_style);
        oci_bind_by_name($stmtBarcode1, ':text_font_size', $text_font_size);
		oci_bind_by_name($stmtBarcode2, ':barcode_level_type_code',  $barcode_level_type_code);
        oci_bind_by_name($stmtBarcode3, ':barcode_page_type_cd', $barcode_page_type_cd);
		oci_bind_by_name($stmtBarcode4, ':barcode_type_description', $barcode_type_description);
		//oci_bind_by_name($stmtBarcode, ':mod_user',  $mod_user);
                
		// Execute Statement
		$barcode_element_return1 = oci_execute($stmtBarcode1);
        $barcode_element_return2 = oci_execute($stmtBarcode2);
        $barcode_element_return3 = oci_execute($stmtBarcode3);
        $barcode_element_return4 = oci_execute($stmtBarcode4);

		oci_free_statement($stmtBarcode1);
        oci_free_statement($stmtBarcode2);
        oci_free_statement($stmtBarcode3);
        oci_free_statement($stmtBarcode4);
		include('/usr/local/apache2/htdocs-ssl/dbinclude/dbclose.php');
		if($barcode_element_return1 == 1 || $barcode_element_return2 == 1 || $barcode_element_return3 == 1 || $barcode_element_return4 == 1){
			$success="Barcodes";
		}
	   }catch(Exception $e){?>
			 <div class="merge-result fade" align="center" style="position: static; width: 100%; float: right; background: #B5EAAA; color: #306754; border: 1px solid #347C17;display: block !important; ">  
				<p><?php echo $e->getMessage();?></p>
			 </div>
<?php   }
}?>