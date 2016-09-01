<?php session_start();

//schema - obieewk, environment - $_SESSION['environment']
        include($_SERVER['DOCUMENT_ROOT'].'/dbinclude/lms.php');
        include($_SERVER['DOCUMENT_ROOT'].'/dbinclude/dbconnect.php');	
if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}else {
    if (isset($_POST['newbarcode'])) {
        unset($_SESSION['barcodeName']);
    }
    if (isset($_POST['barcodeName']) ||isset($_POST['letter_barcode']) || isset($_SESSION['barcodeName'])) {
        // The barcode name is from All Barcode Name
    	if (isset($_POST['barcodeName'])) {
        	$_SESSION['barcodeName'] = $_POST['barcodeName'];
        }
        // The barcode name is coming as an array from Letter Barcodes
        elseif (isset($_POST['letter_barcode'])){
        	$_SESSION['barcodeName'] = $_POST['letter_barcode'][0];
        }
        $selected_barcode = $_SESSION['barcodeName'];
        //select stmt to table
        $strBarcode = "select barcode.*, barcodelevel.barcode_level_type_code, barcodepage.barcode_page_type_cd , barcodepage.barcode_page_type_description, barcodetype.barcode_type_description, barcodetype.barcode_type_id
                        from letter_barcode  barcode, letter_barcode_level_type barcodelevel, letter_barcode_page_type barcodepage, letter_barcode_type barcodetype 
                        where barcode.barcode_level_type_id = barcodelevel.barcode_level_type_id and
                        barcode.barcode_page_type_id = barcodepage.letter_barcode_page_type_id and
                        barcode.barcode_type_id = barcodetype.barcode_type_id and upper(barcode_name)=upper('" . $selected_barcode . "')";

        $objParse = oci_parse($conn, $strBarcode);
        oci_execute($objParse, OCI_DEFAULT);
        oci_fetch_all($objParse, $matchResult);
    }
    
    $globalLink = "select link.barcode_element_order, link.length, link.pad, link.description des, link.batch_variable_id, link.file_position, global.global_name, variable.batch_variable_name
                        from letter_barcode barcode, letter_barcode_global_link link, letter_element_global global, letter_batch_variable variable  
                        where barcode.barcode_id=link.barcode_id and link.global_id = global.global_id and link.batch_variable_id = variable.batch_variable_id and
                        upper(barcode_name)= upper('" . $selected_barcode . "')";

        $objGlobalLink = oci_parse($conn, $globalLink);
        oci_execute($objGlobalLink, OCI_DEFAULT);

        $num_global_link = oci_fetch_all($objGlobalLink, $globalLinkResults);
        
     $strSQL = "select global_name from letter_element_global where (global_name NOT LIKE '%SWR%') order by upper(global_name)";

    $objGB = oci_parse($conn, $strSQL);
    oci_execute($objGB, OCI_DEFAULT);
    oci_fetch_all($objGB, $result);
    
     $typeQuery ="select distinct barcode_type_id, barcode_type_description from letter_barcode_type";
    $objtype = oci_parse($conn, $typeQuery);
    oci_execute ($objtype,OCI_DEFAULT);
    $typeCount=oci_fetch_all($objtype, $typeResult);
    
    $levelQuery ="select * from letter_barcode_level_type";
    $objlevel = oci_parse($conn, $levelQuery);
    oci_execute ($objlevel,OCI_DEFAULT);
    $levelCount=oci_fetch_all($objlevel, $levelResult);
    
    $pageQuery ="select * from letter_barcode_page_type";
    $objpage = oci_parse($conn, $pageQuery);
    oci_execute ($objpage,OCI_DEFAULT);
    $pageCount=oci_fetch_all($objpage, $pageResult);
    
     if(isset($_POST['edit-barcode'])) {
        if($_POST['pwd']== null) {
            $error = "Please enter your password to edit barcode";
        }
    }
     if (isset($error) && !isset($error1)) { ?>
        <div class="merge-result"><p align='center' style='color: red'><?=$error?></p></div>
    <?php }
    
    if(isset($_POST['edit-barcode'])) {
        $query="select w.* from worker w where w.WORKER_ID = :userid and w.password = :passcode";
        $objParse = oci_parse($conn,$query);
        oci_bind_by_name($objParse, ":userid", $_SESSION['userid']);
        oci_bind_by_name($objParse, ":passcode", htmlentities(base64_encode(pack("H*",md5($_POST['pwd'])))));
        oci_execute($objParse,OCI_DEFAULT);
        $matchCount=oci_fetch_all($objParse, $Result);			
        // If result matched WORKER_ID and password, match count must be 1 			
        if($matchCount == 1){ ?>
            <form id='merge-barcode' name="merge_barcode" class='new-edit-widget-bc' action='#' method='post'>
            <div id="vitual-image" style="width: 95%; height: 100%;">
               <span style ="float: left; margin:0 7px 50px 0;">
                   <img style="width: 95%; height: 100%;  position: relative;" src = "support/images/grid.jpg">
                   <img alt="" src="support/images/barcode.png" id="barcode" style="width:150px; height:10px; position: absolute;">
               </span>
               <br>
				X co ordinate: <input type="text" id="barcode_x" value="">
				Y co ordinate: <input type="text" id="barcode_y" value="">
				Rotation <input type="text" id="rotation" value="">
				<div id="myDiv" style="border:1px solid red;">0.0</div>
				<div style="position:relative;">
				  <div id="transperent" style="position:absolute;"></div>
				  <div id="transperent2" style="position:absolute;"></div>
				     </div>   
				<div id="myDiv2" style="border:1px solid red;">-</div>
               
            </div>
            <table class="new-edit-widget-bc">
            <tr>
                <td colspan="3">
                    <table style="border-width: 0px;">
                        <tr>
                            <td width="120px;">
                                <img src="support/images/required.gif" />
                                <label>Barcode Name</label>
                            </td>
                            <td>
                                <label><?= $_POST['barcode_name']; ?></label>
                                <input type="hidden" id="barcode_name" name="barcode_name" value="<?= $_POST['barcode_name']; ?>" />
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr style="height: 232px;">
                <td style="width: 336px">
                    <table style="border-width: 0px;">
                        <tr>
                            <td> <label>Barcode X-Coordinates</label> </td>
                            <td>
                                <input type="text" name="barcode_x" id="barcode_x" onkeypress="return isNumberKey(event)" value="<?if(isset($matchResult)){?><?= trim($matchResult['BARCODE_X_COORDINATE'][0]); ?><?}?>"/>
                            </td>
                        </tr>
                        <tr>
                            <td> <label>Barcode Y-Coordinates</label> </td>
                            <td>
                                <input type="text" name="barcode_y" id="barcode_y" onkeypress="return isNumberKey(event)" value="<?if(isset($matchResult)){?><?= trim($matchResult['BARCODE_Y_COORDINATE'][0]); ?><?}?>"/>
                            </td>
                        </tr>
                         <tr>
                        <td> <label>Barcode Rotation</label> </td>
                        <td>
                            <input type="text" name="barcode_rotation" id="barcode_rotation" maxlength="3" onkeypress="return isNumberKey(event)" value="<?if(isset($matchResult)){?><?= trim($matchResult['BARCODE_ROTATION'][0]); ?><?}?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td> <label>Barcode Width</label> </td>
                        <td>
                            <input type="text" name="barcode_width" id="barcode_width" maxlength="16" min="10" onkeypress="return isNumberKey(event)" value="<?if(isset($matchResult)){?><?= trim($matchResult['BARCODE_WIDTH'][0]); ?><?}?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td> <label>Barcode Height</label> </td>
                        <td>
                            <input type="text" name="barcode_height" id="barcode_height" maxlength="20" min="10" onkeypress="return isNumberKey(event)" value="<?if(isset($matchResult)){?><?= trim($matchResult['BARCODE_HEIGHT'][0]); ?><?}?>"/>
                        </td>
                    </tr>
                    <tr>
                       <td></td>
                       <td style=" float: right; " >
                           <input type="button" id="virtual-barcode" style="text-decoration:none; padding: 3px; " class="gobutton" value="Virtual Barocode" >
                       </td>
                    </tr>
                </table>
            </td>
            <td style="width: 380px">
                <table style="border-width: 0px;">
                    <tr>
                        <td> <img src="support/images/required.gif" /> 
                            <label>What type of barcode is this?</label> </td>
                        <!-- Readonly Barcode type until clicked edit -->
                        <td>
                            <select name="barcode_type" id="barcode_type">
                            <?php for($i=0;$i<$typeCount;$i++){ 
                                    if( trim($_POST['barcode_type']) == trim($typeResult['BARCODE_TYPE_DESCRIPTION'][$i]) ) {?>
                                        <option selected="selected" value="<?=$_POST['barcode_type'];?>" ><?=$_POST['barcode_type'];?></option>									
                              <?php } else {?>	
                                        <option value="<?=trim($typeResult['BARCODE_TYPE_DESCRIPTION'][$i]);?>" ><?=trim($typeResult['BARCODE_TYPE_DESCRIPTION'][$i]);?></option>																							
                              <?php }
                            } ?>	
                            </select>
                        </td>
                        <td>
                            <!--<input type="submit" id="sample_type" value="Sample Type" />-->
                            <a id="sample_type" style="text-decoration:none; padding: 3px;" class="gobutton" href="sample_type.php?id=<?=$matchResult['BARCODE_TYPE_ID'][0];?>" target="_blank">Sample</a>
                        </td>
                    </tr>
                    <tr>
                        <td> <label>When will the barcode be placed on the letter?</label> </td>
                        <td colspan="2">
                            <select name="barcode_level" id="barcode_level">
                            <?php for($i=0;$i<$levelCount;$i++){ 
                                    if( trim($_POST['barcode_level']) == trim($levelResult['BARCODE_LEVEL_TYPE_CODE'][$i]) ) {?>
                                        <option selected="selected" value="<?=$_POST['barcode_level'];?>" ><?=$_POST['barcode_level'];?></option>									
                                <?php } else {?>	
                                        <option value="<?=trim($levelResult['BARCODE_LEVEL_TYPE_CODE'][$i]);?>" ><?=trim($levelResult['BARCODE_LEVEL_TYPE_CODE'][$i]);?></option>																							
                                <?php }
                            } ?>	
                            </select>	
                        </td>
                    </tr>
                    <tr>
                        <td> <label>What page will the barcode be placed?</label> </td>
                        <td colspan="2">
                             <select name="barcode_appear" id="barcode_appear" style="width: 143px;">
                            <?php for($i=0;$i<$pageCount;$i++){ 
                                    if( trim($_POST['barcode_appear']) == trim($pageResult['BARCODE_PAGE_TYPE_CD'][$i]) ) {?>
                                        <option selected="selected" value="<?=$_POST['barcode_appear'];?>" ><?=$_POST['barcode_appear'];?></option>									
                                <?php } else {?>	
                                        <option title="<?=trim($pageResult['BARCODE_PAGE_TYPE_DESCRIPTION'][$i]); ?>" value="<?=trim($pageResult['BARCODE_PAGE_TYPE_CD'][$i]);?>" ><?=trim($pageResult['BARCODE_PAGE_TYPE_CD'][$i]);?></option>																							
                                <?php }
                            } ?>	
                            </select>	
                        </td>
                    </tr>                   
                </table>
            </td>
            <td style="width:279px;">
                <div>
                    <label>Text:</label>
                    <?php
                       if($_POST['text_barcode'] == "Y") {
                           $editChecked_Yes = "checked";
                       } else if($_POST['text_barcode'] == "N") {
                           $editChecked_No = "checked";
                       } ?>
                       <input type="radio" id="text-yes" name="text_barcode" <? if($editChecked_Yes){?> checked="checked" <? } ?>  value="Y" />Yes&emsp;
                       <input type="radio" id="text-no"  name="text_barcode" <? if($editChecked_No){?> checked="checked" <? } ?>  value="N" />No
                </div>
                <?php if ($_POST['text_barcode'] == "Y") { ?>
                    <div id="textReq">
                        <table class="new-edit-widget-bc" style="width: 255px;">
                            <tr>
                                <td> <label>Text X-Coordinates</label> </td>
                                <!-- Readonly text x-coordinates until clicked edit -->
                                <td>
                                    <? if (isset($_POST['edit-barcode'])){ ?>
                                    <input type="text" style="width:120px;" name="text_x" id="text_x" onkeypress="return isNumberKey(event)" value="<?if(isset($matchResult)){?><?= trim($matchResult['TEXT_X_COORDINATE'][0]); ?><?}?>"/>
                                    <? } ?>
                                </td>
                            </tr>
                            <tr>
                                <td> <label>Text Y-Coordinates</label> </td>
                                <!-- Readonly text y-coordinates until clicked edit -->
                                <td>
                                    <? if (isset($_POST['edit-barcode'])){ ?>
                                    <input type="text" style="width:120px;" name="text_y" id="text_y" onkeypress="return isNumberKey(event)" value="<?if(isset($matchResult)){?><?= trim($matchResult['TEXT_Y_COORDINATE'][0]); ?><?}?>"/>
                                    <? } ?>
                                </td>
                            </tr>
                            <tr>
                                <td> <label>Text Rotation</label> </td>
                                <!-- Readonly text rotation until clicked edit -->
                                <td>
                                    <? if (isset($_POST['edit-barcode'])){ ?>
                                    <input type="text" style="width:120px;" name="text_rotation" id="text_rotation" maxlength="3" onkeypress="return isNumberKey(event)" value="<?if(isset($matchResult)){?><?= trim($matchResult['TEXT_ROTATION'][0]); ?><?}?>"/>
                                    <? } ?>
                                </td>
                            </tr>
                            <tr>
                                <td> <label>Text Size</label> </td>
                                <!-- Readonly text size until clicked edit -->
                                <td>
                                    <? if (isset($_POST['edit-barcode'])){ ?>
                                    <input type="text" style="width:120px;" name="text_size" id="text_size" maxlength="2" onkeypress="return isNumberKey(event)" value="<?if(isset($matchResult)){?><?= trim($matchResult['TEXT_FONT_SIZE'][0]); ?><?}?>"/>
                                    <? } ?>
                                </td>
                            </tr>
                            <tr>
                                <td> <label>Text Style</label> </td>
                                <td>
                                    <? if (isset($_POST['edit-barcode'])){ ?>
                                    <input type="text" style="width:120px;" name="text_style" id="text_style" value="<?if(isset($matchResult)){?><?= trim($matchResult['TEXT_FONT_STYLE'][0]); ?><?}?>"/>
                                    <? } ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <label>Barcode Description</label>
                <textarea name="barcode_desc" id="barcode_desc" cols="50" rows="5"><?if(isset($matchResult)){?><?= trim($matchResult['DESCRIPTION'][0]); ?><?}?></textarea>
                <input type="button" value="Spell Check" onclick="$Spelling.SpellCheckInWindow('barcode_desc')" />
            </td>
        </tr>
        <tr>
            <?php if($_POST['barcode_name'] != 'AUDIT_1' && $_POST['barcode_name'] != 'AUDIT_2') { ?>
                <td colspan="3">
                    <span style="float: left;">
                    <label for="txtKeyword">&nbsp;Filter Globals&nbsp; :&nbsp;</label>	
                    <INPUT NAME=regexp class="text_input">&nbsp;&nbsp;
                    <INPUT TYPE=button class="gobutton" onClick="myfilter.reset();this.form.regexp.value = ''" value="Show All">
                    <INPUT TYPE=button class="gobutton" onClick="myfilter.set(this.form.regexp.value)" value="Filter">
                    </span>
                    <table id="mytable" style="border-width: 0px;">
                        <tr>
                            <td style="width: 430px;">
                                <div style="background: #34495e; color: white; padding: 5px 2px 0px 6px; text-align: bottom; border-top-left-radius: 3px; border-top-right-radius: 3px;"><label>Globals :</label></div>
                                <select name="barcodeGlobals" id="barcodeGlobals" size="7" style="width:430px">
                                    <? foreach($result as $row){ 
                                    foreach ($row as $item) {?>
                                    <option value='<?= $item; ?>'><?= $item; ?></option>   
                                    <?} 
                                    }?>    
                                </select>	
                            </td>
                             <SCRIPT TYPE="text/javascript">
                                var myfilter = new filterlist(document.merge_barcode.barcodeGlobals);
                            </SCRIPT>
                            <td style="vertical-align:middle;">
                                <input id="addButton" type="button" value='Add to Barcode' />
                            </td>
                        </tr>
                    </table> 
                    <?php if($_POST['barcode_name'] != 'AUDIT_1' && $_POST['barcode_name'] != 'AUDIT_2' && isset($_POST['edit-barcode'])) { ?>
                        <table class="order-list">
                            <thead>
                            <th style="color: #000; background-color: #E8A317;">Global Name</th>
                            <th style="color: #000; background-color: #E8A317;">Order</th>
                            <th style="color: #000; background-color: #E8A317;">Length</th>
                            <th style="color: #000; background-color: #E8A317;">Pad Left or Right</th>
                            <th style="color: #000; background-color: #E8A317;">Batch Variable</th>
                            <th style="color: #000; background-color: #E8A317;">File Position</th>
                            <th style="color: #000; background-color: #E8A317;">Description</th>
                            <th style="color: #000; background-color: #E8A317;"></th>
                            </thead>
                             <tbody> 
                                <td><?= trim($globalLinkResults['GLOBAL_NAME'][0]); ?></td>
                                <td><input value="<?= trim($globalLinkResults['BARCODE_ELEMENT_ORDER'][0]); ?>" type="text" style="width: 45px;" maxlength="3" name="order" id="order" onkeypress="return isNumberKey(event)"/></td>
                                <td><input value="<?= trim($globalLinkResults['LENGTH'][0]); ?>" type="text" style="width: 45px;" maxlength="3" name="length' + counter + '" id="length" onkeypress="return isNumberKey(event)"/></td>
                               <?php 
                                if ($globalLinkResults['PAD'][0] == "L") {
                                    $pad_left = "checked";
                                } else if ($globalLinkResults['PAD'][0] == "R") {
                                    $pad_right = "checked";
                                }
                                ?>			    
                                <td><input type="radio" name="<?= trim($globalLinkResults['GLOBAL_ID'][0]);?>_pad" class="pad" id="<?= trim($globalLinkResults['GLOBAL_ID'][0]);?>_left" <? if($pad_left){?> checked="checked" <? } ?> value="L"/>Left <br/>
                                    <input type="radio" name="<?= trim($globalLinkResults['GLOBAL_ID'][0]);?>_pad" class="pad" id="<?= trim($globalLinkResults['GLOBAL_ID'][0]);?>_right" <? if($pad_right){?> checked="checked" <? } ?> value="R"/>Right</td>
                                <td><input value="<?= trim($globalLinkResults['BATCH_VARIABLE_NAME'][0]); ?>" style="width: 60px;" name="batch" id="batch" /></td>
                                <td><input value="<?= trim($globalLinkResults['FILE_POSITION'][0]); ?>" style="width: 60px;" name="file_position" id="file_position" /></td>
                                <td><textarea name="desc" id="desc" rows="1" cols="40"><?= trim($globalLinkResults['DES'][0]); ?></textarea></td>
                                <td><button class="ImgDelete" id="deleteButton">Delete</button></a></td>
                            </tbody>
                            <tfoot>
                                <tr><td colspan="8"></td></tr>
                                <tr>
                                    <td colspan="8">
                                        <label>Total length of Barcode:</label>
                                        <label name="grandtotal" id="grandtotal"></label>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                        <?php } else if($_POST['barcode_name'] != 'AUDIT_1' && $_POST['barcode_name'] != 'AUDIT_2'){ ?>
                            <table class="order-list">
                                <thead>
                                    <th style="color: #000; background-color: #E8A317;">Global Name</th>
                                    <th style="color: #000; background-color: #E8A317;">Order</th>
                                    <th style="color: #000; background-color: #E8A317;">Length</th>
                                    <th style="color: #000; background-color: #E8A317;">Pad Left or Right</th>
                                    <th style="color: #000; background-color: #E8A317;">Batch Variable</th>
                                    <th style="color: #000; background-color: #E8A317;">File Position</th>
                                    <th style="color: #000; background-color: #E8A317;">Description</th>
                                    <th style="color: #000; background-color: #E8A317;"></th>
                                </thead>
                                <tfoot>
                                    <tr><td colspan="8"></td></tr>
                                    <tr>
                                        <td colspan="8">
                                            <label>Total length of Barcode:</label>
                                            <label name="grandtotal" id="grandtotal"></label>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        <?php }?> 
                    </td>  
                    <?php } else { ?>
                    <td colspan="3">
                        <table class="order-list">
                            <thead>
                                <th style="color: #000; background-color: #E8A317;">Global Name</th>
                                <th style="color: #000; background-color: #E8A317;">Order</th>
                                <th style="color: #000; background-color: #E8A317;">Length</th>
                                <th style="color: #000; background-color: #E8A317;">Pad Left or Right</th>
                                <th style="color: #000; background-color: #E8A317;">Batch Variable</th>
                                <th style="color: #000; background-color: #E8A317;">File Position</th>
                                <th style="color: #000; background-color: #E8A317;">Description</th>
                                <th style="color: #000; background-color: #E8A317;"></th>
                            </thead>
                             <tbody>
                                 <tr>
                                    <td><?= trim($globalLinkResults['GLOBAL_NAME'][0]); ?></td>
                                    <td><input value="<?= trim($globalLinkResults['BARCODE_ELEMENT_ORDER'][0]); ?>" type="text" style="width: 45px;" maxlength="3" name="order" id="order" onkeypress="return isNumberKey(event)"/></td>
                                    <td><input value="<?= trim($globalLinkResults['LENGTH'][0]); ?>" type="text" style="width: 45px;" maxlength="3" name="length' + counter + '" id="length" onkeypress="return isNumberKey(event)"/></td>
                                   <?php 
                                    if ($globalLinkResults['PAD'][0] == "L") {
                                        $pad_left = "checked";
                                    } else if ($globalLinkResults['PAD'][0] == "R") {
                                        $pad_right = "checked";
                                    }
                                    ?>			    
                                    <td><input type="radio" name="<?= trim($globalLinkResults['GLOBAL_ID'][0]);?>_pad" class="pad" id="<?= trim($globalLinkResults['GLOBAL_ID'][0]);?>_left" <? if($pad_left){?> checked="checked" <? } ?> value="L"/>Left <br/>
                                        <input type="radio" name="<?= trim($globalLinkResults['GLOBAL_ID'][0]);?>_pad" class="pad" id="<?= trim($globalLinkResults['GLOBAL_ID'][0]);?>_right" <? if($pad_right){?> checked="checked" <? } ?> value="R"/>Right</td>
                                    <td><input value="<?= trim($globalLinkResults['BATCH_VARIABLE_NAME'][0]); ?>" style="width: 60px;" name="batch" id="batch" /></td>
                                    <td><input value="<?= trim($globalLinkResults['FILE_POSITION'][0]); ?>" style="width: 60px;" name="file_position" id="file_position" /></td>
                                    <td><textarea name="desc" id="desc" rows="1" cols="40"><?= trim($globalLinkResults['DES'][0]); ?></textarea></td>
                                 </tr>
                            </tbody>
                       </table>
                   </td>
                    <?php } ?>
                </tr>
                <tr>
                    <td colspan="3">
                        <input name="sample" type="button" id="sample" value="SAMPLE"/> <br/><br/>
                        <label for="barcode_sample">Sample Barcode: </label>
                        <label name="barcode_sample" id="barcode_sample" style="font-size: 15px;"></label>
                    </td>
                </tr>
                <tr> 
                    <td colspan="3"> 
                        <div class="test" style="text-align:center"> 
                            <input name="reset-edit" type="reset" class="nav-button" value=" UNDO ALL "/>
                            <input name="save-barcode" type="submit" class="nav-button" id="save-barcode" value=" SAVE "/>
                            <input name="cancel-edit" type="submit" class="nav-button" value=" CANCEL "/>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    <?php  } else {  
            $error1="Login Failure: Error in Password provided";
        }
    }
    if (isset($error1)&& !isset($error)) { ?>
        <div class="merge-result"><p align='center' style='color: red'><?=$error1?></p></div>
    <?php }
		include('/usr/local/apache2/htdocs-ssl/dbinclude/dbclose.php');
  }
?>
<script type="text/javascript">
            $Spelling.SpellCheckAsYouType('barcode_desc');
    $(document).ready(function () {
       $("table.order-list").on("change", 'input[name^="length"]', function (event) {       
            calculateGrandTotal();
        });
    });
    $('#textReq').hide();
    $('input[name="text_barcode"]').bind('change', function() {
            var showOrHide = ($(this).val() == 'Y') ? true : false;
            $('#textReq').toggle(showOrHide);
        });
        
</script>
<?php if(!isset($_POST['edit-barcode'])) { ?>
<form id='merge-barcode' name="merge_barcode" class='new-edit-widget-bc' action='#merge-barcode' method='post'>
        <table class="new-edit-widget-bc">
        <tr>
            <td colspan="3">
                <table style="border-width: 0px;">
                    <tr>
                        <td width="120px;">
                            <img src="support/images/required.gif" />
                            <label>Barcode Name</label>
                        </td>
                        <td>
                            <? if(isset($matchResult)) { 
                            if((!isset($_POST['cancel-copy'])) && isset($_POST['barcode_name']) && ($_POST['barcode_name']=='' )){?>
                            <input class="text_input" type="text" name="barcode_name" id="barcode_name" />
                            <? } else{?>
                            <label><?= $matchResult['BARCODE_NAME'][0]; ?></label>
                            <input type="hidden" id="barcode_name" name="barcode_name" value="<?= trim($matchResult['BARCODE_NAME'][0]); ?>" />								
                            <? }
                            } else{ ?>
                            <input class="text_input" type="text" name="barcode_name" id="barcode_name" />
                            <? } ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr style="height: 232px;">
            <td style="width: 336px">
                <table style="border-width: 0px;">
                    <tr>
                        <td> <label>Barcode X-Coordinates</label> </td>
                        <!-- Readonly Barcode x-coordinates until clicked edit -->
                        <td>
                            <? if (isset($_POST['newbarcode'])){ ?>
                            <input type="text" name="barcode_x" id="barcode_x" onkeypress="return isNumberKey(event)" value="<?if(isset($matchResult)){?><?= trim($matchResult['BARCODE_X_COORDINATE'][0]); ?><?}?>"/>
                            <? } else if(isset($matchResult)) { ?>
                            <input type="text" name="barcode_x" id="barcode_x" readonly="readonly"  value="<?= trim($matchResult['BARCODE_X_COORDINATE'][0]); ?>"/>								
                            <? } else { ?>	
                            <input type="text" id="barcode_x" name="barcode_x" onkeypress="return isNumberKey(event)"/>
                            <? } ?>
                        </td>
                    </tr>
                    <tr>
                        <td> <label>Barcode Y-Coordinates</label> </td>
                        <!-- Readonly Barcode y-coordinates until clicked edit -->
                        <td>
                            <? if (isset($_POST['newbarcode'])){ ?>
                            <input type="text" name="barcode_y" id="barcode_y" onkeypress="return isNumberKey(event)" value="<?if(isset($matchResult)){?><?= trim($matchResult['BARCODE_Y_COORDINATE'][0]); ?><?}?>"/>
                            <? } else if(isset($matchResult)) { ?>
                            <input type="text" name="barcode_y" id="barcode_y" readonly="readonly"  value="<?= trim($matchResult['BARCODE_Y_COORDINATE'][0]); ?>"/>								
                            <? } else { ?>	
                            <input type="text" id="barcode_y" name="barcode_y" onkeypress="return isNumberKey(event)"/>
                            <? } ?>
                        </td>
                    </tr>
                    <tr>
                        <td> <label>Barcode Rotation</label> </td>
                        <!-- Readonly Barcode rotation until clicked edit -->
                        <td>
                            <? if (isset($_POST['newbarcode'])){ ?>
                            <input type="text" name="barcode_rotation" id="barcode_rotation" maxlength="3" onkeypress="return isNumberKey(event)" value="<?if(isset($matchResult)){?><?= trim($matchResult['BARCODE_ROTATION'][0]); ?><?}?>"/>
                            <? } else if(isset($matchResult)) { ?>
                            <input type="text" name="barcode_rotation" id="barcode_rotation" readonly="readonly"  value="<?= trim($matchResult['BARCODE_ROTATION'][0]); ?>"/>								
                            <? } else { ?>	
                            <input type="text" id="barcode_rotation" name="barcode_rotation" maxlength="3" onkeypress="return isNumberKey(event)"/>
                            <? } ?>
                        </td>
                    </tr>
                    <tr>
                        <td> <label>Barcode Width</label> </td>
                        <!-- Readonly Barcode width until clicked edit -->
                        <td>
                            <? if (isset($_POST['newbarcode'])){ ?>
                            <input type="text" name="barcode_width" id="barcode_width" maxlength="16" min="10" onkeypress="return isNumberKey(event)" value="<?if(isset($matchResult)){?><?= trim($matchResult['BARCODE_WIDTH'][0]); ?><?}?>"/>
                            <? } else if(isset($matchResult)) { ?>
                            <input type="text" name="barcode_width" id="barcode_width" readonly="readonly"  value="<?= trim($matchResult['BARCODE_WIDTH'][0]); ?>"/>								
                            <? } else { ?>	
                            <input type="text" id="barcode_width" name="barcode_width" maxlength="16" min="10" onkeypress="return isNumberKey(event)"/>
                            <? } ?>
                        </td>
                    </tr>
                    <tr>
                        <td> <label>Barcode Height</label> </td>
                        <!-- Readonly Barcode height until clicked edit -->
                        <td>
                            <? if (isset($_POST['newbarcode'])){ ?>
                            <input type="text" name="barcode_height" id="barcode_height" maxlength="20" min="10" onkeypress="return isNumberKey(event)" value="<?if(isset($matchResult)){?><?= trim($matchResult['BARCODE_HEIGHT'][0]); ?><?}?>"/>
                            <? } else if(isset($matchResult)) { ?>
                            <input type="text" name="barcode_height" id="barcode_height" readonly="readonly"  value="<?= trim($matchResult['BARCODE_HEIGHT'][0]); ?>"/>								
                            <? } else { ?>	
                            <input type="text" id="barcode_height" name="barcode_height" maxlength="20" min="10" onkeypress="return isNumberKey(event)"/>
                            <? } ?>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 380px">
                <table style="border-width: 0px;">
                    <tr>
                        <td> <img src="support/images/required.gif" /> 
                            <label>What type of barcode is this?</label> </td>
                        <!-- Readonly Barcode type until clicked edit -->
                        <td>
                            <select name="barcode_type" id="barcode_type">
                            <?php  if(isset($matchResult)) {
                                    for($i=0;$i<$typeCount;$i++){ 
                                        if( trim($matchResult['BARCODE_TYPE_DESCRIPTION'][0]) == trim($typeResult['BARCODE_TYPE_DESCRIPTION'][$i]) ) {?>
                                                <option selected="selected" value="<?=trim($matchResult['BARCODE_TYPE_DESCRIPTION'][0]);?>" ><?=trim($matchResult['BARCODE_TYPE_DESCRIPTION'][0]);?></option>									
                                        <?php } else {?>	
                                                <option value="<?=trim($typeResult['BARCODE_TYPE_DESCRIPTION'][$i]);?>" ><?=trim($typeResult['BARCODE_TYPE_DESCRIPTION'][$i]);?></option>																							
                                        <?php }
                                    }
                                } else if( isset($_POST['barcode_name'])){ ?>
                                    <option value="<?=$matchResult['BARCODE_TYPE_DESCRIPTION'][0];?>" ><?=$matchResult['BARCODE_TYPE_DESCRIPTION'][0];?></option>
                            <?php } else { ?>
                                    <option value="" disabled="disabled" selected="selected">Select barcode type</option>
                                    <?php for($i=0;$i<$typeCount;$i++){ ?>
                                            <option value="<?=$typeResult['BARCODE_TYPE_DESCRIPTION'][$i];?>" ><?=$typeResult['BARCODE_TYPE_DESCRIPTION'][$i];?></option>
                                    <?php }
                            }?>	
                            </select>
                        </td>
                        <td>
                            <!--<input type="submit" id="sample_type" value="Sample Type" />-->
                            <a id="sample_type" style="text-decoration:none; padding: 3px;" class="gobutton" href="sample_type.php?id=<?=$matchResult['BARCODE_TYPE_ID'][0];?>" target="_blank">Sample</a>
                        </td>
                    </tr>
                    <tr>
                        <td> <label>When will the barcode be placed on the letter?</label> </td>
                        <!-- Readonly Barcode level until clicked edit -->
                        <td colspan="2">
                            <select name="barcode_level" id="barcode_level">
                            <?php if(isset($matchResult)) {
                                    for($i=0;$i<$levelCount;$i++){ ?>	
                                            <?php if( trim($matchResult['BARCODE_LEVEL_TYPE_CODE'][0]) == trim($levelResult['BARCODE_LEVEL_TYPE_CODE'][$i]) ) {?>
                                                    <option selected="selected" value="<?=trim($matchResult['BARCODE_LEVEL_TYPE_CODE'][0]);?>" ><?=trim($matchResult['BARCODE_LEVEL_TYPE_CODE'][0]);?></option>									
                                            <?php } else {?>	
                                                    <option value="<?=trim($levelResult['BARCODE_LEVEL_TYPE_CODE'][$i]);?>" ><?=trim($levelResult['BARCODE_LEVEL_TYPE_CODE'][$i]);?></option>																							
                                            <?php }
                                    }
                                } else if( isset($_POST['barcode_name'])){ ?>
                                    <option value="<?=$matchResult['BARCODE_LEVEL_TYPE_CODE'][0];?>" ><?=$matchResult['BARCODE_LEVEL_TYPE_CODE'][0];?></option>
                            <?php } else { ?>
                                    <option value="" disabled="disabled" selected="selected">Select barcode level</option>
                                    <?php for($i=0;$i<$levelCount;$i++){ ?>
                                            <option value="<?=$levelResult['BARCODE_LEVEL_TYPE_CODE'][$i];?>" ><?=$levelResult['BARCODE_LEVEL_TYPE_CODE'][$i];?></option>
                                    <?php }
                            }?>	
                            </select>	
                        </td>
                    </tr>
                    <tr>
                        <td> <label>What page will the barcode be placed?</label> </td>
                        <!-- Readonly Barcode appearance until clicked edit -->
                        <td colspan="2">
                             <select name="barcode_appear" id="barcode_appear" style="width: 143px;">
                            <?php if(isset($matchResult)) {
                                    for($i=0;$i<$pageCount;$i++){ ?>	
                                            <?php if( trim($matchResult['BARCODE_PAGE_TYPE_CD'][0]) == trim($pageResult['BARCODE_PAGE_TYPE_CD'][$i]) ) {?>
                                                    <option title="<?=trim($matchResult['BARCODE_PAGE_TYPE_DESCRIPTION'][0]); ?>" selected="selected" value="<?=trim($matchResult['BARCODE_PAGE_TYPE_CD'][0]);?>" ><?=trim($matchResult['BARCODE_PAGE_TYPE_CD'][0]);?></option>									
                                            <?php } else {?>	
                                                    <option title="<?=trim($pageResult['BARCODE_PAGE_TYPE_DESCRIPTION'][$i]); ?>" value="<?=trim($pageResult['BARCODE_PAGE_TYPE_CD'][$i]);?>" ><?=trim($pageResult['BARCODE_PAGE_TYPE_CD'][$i]);?></option>																							
                                            <?php }
                                    }
                                } else if( isset($_POST['barcode_name'])){ ?>
                                    <option title="<?=trim($matchResult['BARCODE_PAGE_TYPE_DESCRIPTION'][0]); ?>" value="<?=$matchResult['BARCODE_PAGE_TYPE_CD'][0];?>" ><?=$matchResult['BARCODE_PAGE_TYPE_CD'][0];?></option>
                            <?php } else { ?>
                                    <option title="If last page is blank, Barcode will not be placed on blank page." disabled="disabled" selected="selected">Select page type</option>
                                    <?php for($i=0;$i<$pageCount;$i++){ ?>
                                            <option title="<?=trim($pageResult['BARCODE_PAGE_TYPE_DESCRIPTION'][$i]); ?>" value="<?=$pageResult['BARCODE_PAGE_TYPE_CD'][$i];?>" ><?=$pageResult['BARCODE_PAGE_TYPE_CD'][$i];?></option>
                                    <?php }
                            }?>	
                            </select>	
                        </td>
                    </tr>                   
                </table>
            </td>
            <td style="width:279px;">
                <div>
                    <label>Text:</label>
                    <?php
                    if (isset($matchResult)) {
                        if ($matchResult['TEXT'][0] == "Y") {
                            $check_yes = "checked";
                        } else {
                            $check_no = "checked";
                        }
                        ?>			    
                        <input type="radio" id="text-yes" name="text_barcode" <? if($check_yes){?> checked="checked" <? } ?>  value="Y" />Yes&emsp;
                               <input type="radio" id="text-no"  name="text_barcode" <? if($check_no){?> checked="checked" <? } ?>  value="N" />No
                           <?php } else { ?>
                               <input type="radio" id="text-yes"  name="text_barcode" value="Y" />Yes&emsp;
                        <input type="radio" id="text-no"  name="text_barcode" value="N" />No
                    <?php } ?>
                </div>
                <?php if (($matchResult['TEXT'][0] == "Y") || (isset($_POST['newbarcode']))) { ?>
                    <div id="textReq">
                        <table class="new-edit-widget-bc" style="width: 255px;">
                            <tr>
                                <td> <label>Text X-Coordinates</label> </td>
                                <!-- Readonly text x-coordinates until clicked edit -->
                                <td>
                                    <? if (isset($_POST['newbarcode'])){ ?>
                                    <input type="text" style="width:120px;" name="text_x" id="text_x" onkeypress="return isNumberKey(event)" value="<?if(isset($matchResult)){?><?= trim($matchResult['TEXT_X_COORDINATE'][0]); ?><?}?>"/>
                                    <? } else if(isset($matchResult)) { ?>
                                    <input type="text" style="width:120px;" name="text_x" id="text_x" readonly="readonly" value="<?= trim($matchResult['TEXT_X_COORDINATE'][0]); ?>"/>								
                                    <? } else { ?>	
                                    <input type="text" style="width:120px;" id="text_x" name="text_x" onkeypress="return isNumberKey(event)"/>
                                    <? } ?>
                                </td>
                            </tr>
                            <tr>
                                <td> <label>Text Y-Coordinates</label> </td>
                                <!-- Readonly text y-coordinates until clicked edit -->
                                <td>
                                    <? if (isset($_POST['newbarcode'])){ ?>
                                    <input type="text" style="width:120px;" name="text_y" id="text_y" onkeypress="return isNumberKey(event)" value="<?if(isset($matchResult)){?><?= trim($matchResult['TEXT_Y_COORDINATE'][0]); ?><?}?>"/>
                                    <? } else if(isset($matchResult)) { ?>
                                    <input type="text" style="width:120px;" name="text_y" id="text_y" readonly="readonly"  value="<?= trim($matchResult['TEXT_Y_COORDINATE'][0]); ?>"/>								
                                    <? } else { ?>	
                                    <input type="text" style="width:120px;" id="text_y" name="text_y" onkeypress="return isNumberKey(event)"/>
                                    <? } ?>
                                </td>
                            </tr>
                            <tr>
                                <td> <label>Text Rotation</label> </td>
                                <!-- Readonly text rotation until clicked edit -->
                                <td>
                                    <? if (isset($_POST['newbarcode'])){ ?>
                                    <input type="text" style="width:120px;" name="text_rotation" id="text_rotation" maxlength="3" onkeypress="return isNumberKey(event)" value="<?if(isset($matchResult)){?><?= trim($matchResult['TEXT_ROTATION'][0]); ?><?}?>"/>
                                    <? } else if(isset($matchResult)) { ?>
                                    <input type="text" style="width:120px;" name="text_rotation" id="text_rotation" readonly="readonly"  value="<?= trim($matchResult['TEXT_ROTATION'][0]); ?>"/>								
                                    <? } else { ?>	
                                    <input type="text" style="width:120px;" id="text_rotation" maxlength="3" name="text_rotation" maxlength="3" onkeypress="return isNumberKey(event)"/>
                                    <? } ?>
                                </td>
                            </tr>
                            <tr>
                                <td> <label>Text Size</label> </td>
                                <!-- Readonly text size until clicked edit -->
                                <td>
                                    <? if (isset($_POST['newbarcode'])){ ?>
                                    <input type="text" style="width:120px;" name="text_size" id="text_size" maxlength="2" onkeypress="return isNumberKey(event)" value="<?if(isset($matchResult)){?><?= trim($matchResult['TEXT_FONT_SIZE'][0]); ?><?}?>"/>
                                    <? } else if(isset($matchResult)) { ?>
                                    <input type="text" style="width:120px;" name="text_size" id="text_size" readonly="readonly"  value="<?= trim($matchResult['TEXT_FONT_SIZE'][0]); ?>"/>								
                                    <? } else { ?>	
                                    <input type="text" style="width:120px;" id="text_size" name="text_size" maxlength="2" onkeypress="return isNumberKey(event)" />
                                    <? } ?>
                                </td>
                            </tr>
                            <tr>
                                <td> <label>Text Style</label> </td>
                                <td>
                                    <? if (isset($_POST['newbarcode'])){ ?>
                                    <input type="text" style="width:120px;" name="text_style" id="text_style" value="<?if(isset($matchResult)){?><?= trim($matchResult['TEXT_FONT_STYLE'][0]); ?><?}?>"/>
                                    <? } else if(isset($matchResult)) { ?>
                                    <input type="text" style="width:120px;" name="text_style" id="text_style" readonly="readonly"  value="<?= trim($matchResult['TEXT_FONT_STYLE'][0]); ?>"/>								
                                    <? } else { ?>	
                                    <input type="text" style="width:120px;" id="text_style" name="text_style" />
                                    <? } ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <label>Barcode Description</label>
                <!-- Readonly Description until clicked edit -->
                <? if(isset($_POST['newbarcode'])) { ?>
                <textarea name="barcode_desc" id="barcode_desc" cols="50" rows="5"><?if(isset($matchResult)){?><?= trim($matchResult['DESCRIPTION'][0]); ?><?}?></textarea>
                <input type="button" value="Spell Check" onclick="$Spelling.SpellCheckInWindow('barcode_desc')" />
                <? }else if(isset($matchResult)){ ?>
                <textarea name="barcode_desc" id="barcode_desc" cols="50" rows="5" readonly="readonly"><?= trim($matchResult['DESCRIPTION'][0]); ?></textarea>
                <? } else { ?>	
                <textarea name="barcode_desc" id="barcode_desc" cols="50" rows="5"></textarea>
                <input type="button" value="Spell Check" onclick="$Spelling.SpellCheckInWindow('barcode_desc')" />
                <? } ?>
            </td>
        </tr>
       <tr>
            <?php if(isset($_POST['newbarcode'])) {
                if($_POST['barcode_name'] != 'AUDIT_1' && $_POST['barcode_name'] != 'AUDIT_2') { ?>
                <td colspan="3">
                    <span style="float: left;">
                    <label for="txtKeyword">&nbsp;Filter Globals&nbsp; :&nbsp;</label>	
                    <INPUT NAME=regexp class="text_input">&nbsp;&nbsp;
                    <INPUT TYPE=button class="gobutton" onClick="myfilter.reset();this.form.regexp.value = ''" value="Show All">
                    <INPUT TYPE=button class="gobutton" onClick="myfilter.set(this.form.regexp.value)" value="Filter">
                    </span>
                    <table id="mytable" style="border-width: 0px;">
                        <tr>
                            <td style="width: 430px;">
                                <div style="background: #34495e; color: white; padding: 5px 2px 0px 6px; text-align: bottom; border-top-left-radius: 3px; border-top-right-radius: 3px;"><label>Globals :</label></div>
                                <select name="barcodeGlobals" id="barcodeGlobals" size="7" style="width:430px">
                                    <? foreach($result as $row){ 
                                    foreach ($row as $item) {?>
                                    <option value='<?= $item; ?>'><?= $item; ?></option>   
                                    <?} 
                                    }?>    
                                </select>	
                            </td>
                             <SCRIPT TYPE="text/javascript">
                                var myfilter = new filterlist(document.merge_barcode.barcodeGlobals);
                            </SCRIPT>
                            <td style="vertical-align:middle;">
                                <input id="addButton" type="button" value='Add to Barcode' />
                            </td>
                        </tr>
                    </table> 
                    <?php if($_POST['barcode_name'] != 'AUDIT_1' && $_POST['barcode_name'] != 'AUDIT_2' && isset($_POST['edit-barcode'])) { ?>
                        <table class="order-list">
                            <thead>
                            <th style="color: #000; background-color: #E8A317;">Global Name</th>
                            <th style="color: #000; background-color: #E8A317;">Order</th>
                            <th style="color: #000; background-color: #E8A317;">Length</th>
                            <th style="color: #000; background-color: #E8A317;">Pad Left or Right</th>
                            <th style="color: #000; background-color: #E8A317;">Batch Variable</th>
                            <th style="color: #000; background-color: #E8A317;">File Position</th>
                            <th style="color: #000; background-color: #E8A317;">Description</th>
                            <th style="color: #000; background-color: #E8A317;"></th>
                            </thead>
                             <tbody> 
                                <td><?= trim($globalLinkResults['GLOBAL_NAME'][0]); ?></td>
                                <td><input value="<?= trim($globalLinkResults['BARCODE_ELEMENT_ORDER'][0]); ?>" type="text" style="width: 45px;" maxlength="3" name="order" id="order" onkeypress="return isNumberKey(event)"/></td>
                                <td><input value="<?= trim($globalLinkResults['LENGTH'][0]); ?>" type="text" style="width: 45px;" maxlength="3" name="length' + counter + '" id="length" onkeypress="return isNumberKey(event)"/></td>
                               <?php 
                                if ($globalLinkResults['PAD'][0] == "L") {
                                    $pad_left = "checked";
                                } else if ($globalLinkResults['PAD'][0] == "R") {
                                    $pad_right = "checked";
                                }
                                ?>			    
                                <td><input type="radio" name="<?= trim($globalLinkResults['GLOBAL_ID'][0]);?>_pad" class="pad" id="<?= trim($globalLinkResults['GLOBAL_ID'][0]);?>_left" <? if($pad_left){?> checked="checked" <? } ?> value="L"/>Left <br/>
                                    <input type="radio" name="<?= trim($globalLinkResults['GLOBAL_ID'][0]);?>_pad" class="pad" id="<?= trim($globalLinkResults['GLOBAL_ID'][0]);?>_right" <? if($pad_right){?> checked="checked" <? } ?> value="R"/>Right</td>
                                <td><input value="<?= trim($globalLinkResults['BATCH_VARIABLE_NAME'][0]); ?>" style="width: 60px;" name="batch" id="batch" /></td>
                                <td><input value="<?= trim($globalLinkResults['FILE_POSITION'][0]); ?>" style="width: 60px;" name="file_position" id="file_position" /></td>
                                <td><textarea name="desc" id="desc" rows="1" cols="40"><?= trim($globalLinkResults['DES'][0]); ?></textarea></td>
                                <td><button class="ImgDelete" id="deleteButton">Delete</button></a></td>
                            </tbody>
                            <tfoot>
                                <tr><td colspan="8"></td></tr>
                                <tr>
                                    <td colspan="8">
                                        <label>Total length of Barcode:</label>
                                        <label name="grandtotal" id="grandtotal"></label>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                        <?php } else if($_POST['barcode_name'] != 'AUDIT_1' && $_POST['barcode_name'] != 'AUDIT_2'){ ?>
                            <table class="order-list">
                                <thead>
                                    <th style="color: #000; background-color: #E8A317;">Global Name</th>
                                    <th style="color: #000; background-color: #E8A317;">Order</th>
                                    <th style="color: #000; background-color: #E8A317;">Length</th>
                                    <th style="color: #000; background-color: #E8A317;">Pad Left or Right</th>
                                    <th style="color: #000; background-color: #E8A317;">Batch Variable</th>
                                    <th style="color: #000; background-color: #E8A317;">File Position</th>
                                    <th style="color: #000; background-color: #E8A317;">Description</th>
                                    <th style="color: #000; background-color: #E8A317;"></th>
                                </thead>
                                <tfoot>
                                    <tr><td colspan="8"></td></tr>
                                    <tr>
                                        <td colspan="8">
                                            <label>Total length of Barcode:</label>
                                            <label name="grandtotal" id="grandtotal"></label>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        <?php }?> 
                </td>  
                <?php } else { ?>
                <td colspan="3">
                    <table class="order-list">
                        <thead>
                            <th style="color: #000; background-color: #E8A317;">Global Name</th>
                            <th style="color: #000; background-color: #E8A317;">Order</th>
                            <th style="color: #000; background-color: #E8A317;">Length</th>
                            <th style="color: #000; background-color: #E8A317;">Pad Left or Right</th>
                            <th style="color: #000; background-color: #E8A317;">Batch Variable</th>
                            <th style="color: #000; background-color: #E8A317;">File Position</th>
                            <th style="color: #000; background-color: #E8A317;">Description</th>
                            <th style="color: #000; background-color: #E8A317;"></th>
                        </thead>
                         <tbody>
                             <tr>
                                <td><?= trim($globalLinkResults['GLOBAL_NAME'][0]); ?></td>
                                <td><input value="<?= trim($globalLinkResults['BARCODE_ELEMENT_ORDER'][0]); ?>" type="text" style="width: 45px;" maxlength="3" name="order" id="order" onkeypress="return isNumberKey(event)"/></td>
                                <td><input value="<?= trim($globalLinkResults['LENGTH'][0]); ?>" type="text" style="width: 45px;" maxlength="3" name="length' + counter + '" id="length" onkeypress="return isNumberKey(event)"/></td>
                               <?php 
                                if ($globalLinkResults['PAD'][0] == "L") {
                                    $pad_left = "checked";
                                } else if ($globalLinkResults['PAD'][0] == "R") {
                                    $pad_right = "checked";
                                }
                                ?>			    
                                <td><input type="radio" name="<?= trim($globalLinkResults['GLOBAL_ID'][0]);?>_pad" class="pad" id="<?= trim($globalLinkResults['GLOBAL_ID'][0]);?>_left" <? if($pad_left){?> checked="checked" <? } ?> value="L"/>Left <br/>
                                    <input type="radio" name="<?= trim($globalLinkResults['GLOBAL_ID'][0]);?>_pad" class="pad" id="<?= trim($globalLinkResults['GLOBAL_ID'][0]);?>_right" <? if($pad_right){?> checked="checked" <? } ?> value="R"/>Right</td>
                                <td><input value="<?= trim($globalLinkResults['BATCH_VARIABLE_NAME'][0]); ?>" style="width: 60px;" name="batch" id="batch" /></td>
                                <td><input value="<?= trim($globalLinkResults['FILE_POSITION'][0]); ?>" style="width: 60px;" name="file_position" id="file_position" /></td>
                                <td><textarea name="desc" id="desc" rows="1" cols="40"><?= trim($globalLinkResults['DES'][0]); ?></textarea></td>
                             </tr>
                         </tbody>
                    </table>
                </td>
            <?php } 
            } else if(isset ($globalLinkResults['GLOBAL_NAME'][0]) ){ ?>
                <td colspan="3">
                    <table class="order-list">
                        <thead>
                            <th style="color: #000; background-color: #E8A317;">Global Name</th>
                            <th style="color: #000; background-color: #E8A317;">Order</th>
                            <th style="color: #000; background-color: #E8A317;">Length</th>
                            <th style="color: #000; background-color: #E8A317;">Pad Left or Right</th>
                            <th style="color: #000; background-color: #E8A317;">Batch Variable</th>
                            <th style="color: #000; background-color: #E8A317;">File Position</th>
                            <th style="color: #000; background-color: #E8A317;">Description</th>
                        </thead>
                        <? for ($i=0;$i<$num_global_link;$i++) { ?>
                         <tbody> 
                             <tr>
                                 <td><?= $globalLinkResults['GLOBAL_NAME'][$i]; ?> </td>
                                    <td><input value="<?= $globalLinkResults['BARCODE_ELEMENT_ORDER'][$i]; ?>" readonly="readonly" type="text" style="width: 45px;" maxlength="3" name="order" id="order" onkeypress="return isNumberKey(event)"/></td>
                                    <td><input value="<?= $globalLinkResults['LENGTH'][$i]; ?>" readonly="readonly" type="text" style="width: 45px;" maxlength="3" name="length' + counter + '" id="length" onkeypress="return isNumberKey(event)"/></td>
                                    <td>
                                        <?php 
                                         if ($globalLinkResults['PAD'][$i] == "L") {
                                             $pad_left = "checked";
                                         } else if ($globalLinkResults['PAD'][$i] == "R") {
                                             $pad_right = "checked";
                                         }
                                         ?>			    
                                         <input type="radio" name="<?=$i?>_pad" class="pad" id="<?= $i?>_left" <? if($pad_left){?> checked="checked" <? } else { ?> disabled="disabled" <? } ?> value="L"/>Left <br/>
                                         <input type="radio" name="<?= $i?>_pad" class="pad" id="<?= $i?>_right" <? if($pad_right){?> checked="checked" <? } else { ?> disabled="disabled" <? }?> value="R"/>Right
                                    </td>
                                    <td><input value="<?= trim($globalLinkResults['BATCH_VARIABLE_NAME'][0]); ?>" style="width: 60px;" name="batch" id="batch" /></td>
                                    <td><input value="<?= trim($globalLinkResults['FILE_POSITION'][0]); ?>" style="width: 60px;" name="file_position" id="file_position" /></td>
                                    <td><textarea rows="1" cols="40" name="desc" id="desc" ><?= trim($globalLinkResults['DES'][0]); ?></textarea></td>
                             </tr>
                           </tbody>
                         <? } ?>
                    </table>
                </td>
        <?php  }?> 
        </tr>
        <tr>
            <? if(isset($_POST['newbarcode'])) { ?>
            <td colspan="3">
                <input name="sample" type="button" id="sample" value="SAMPLE"/> <br/><br/>
                <label for="barcode_sample">Sample Barcode: </label>
                <label name="barcode_sample" id="barcode_sample" style="font-size: 15px;"></label>
            </td>
            <? } ?>
        </tr>
        <tr> 
            <td colspan="3"> 
                <div class="test" style="text-align:center"> 
                    <? if(isset($_POST['newbarcode'])) {?>
                    <input name="reset-copy" type="reset" class="nav-button" value=" UNDO ALL "/>
                    <input name="cancel-copy" type="submit" class="nav-button" value=" CANCEL "/>
                    <input name="create-barcode" type="submit" class="nav-button" id="create-barcode" value=" CREATE "/>
                    <?} else { ?>
                    <input name="edit-barcode" type="submit" class="nav-button" id="edit-barcode" value=" EDIT "/>
                    <br/><br/>
                    <div style="border: dashed #E8A317; border-width: 2px; width: 380px; padding: 10px; margin-left: 320px;" >
                        <b>Making changes to an existing barcode will affect all letters.</b> <br/><br/>
                        <img alt="required field" src="support/images/required.gif">
                        <b>Password:</b>
                        <input type="password" id="pwd" name="pwd" />
                    </div>
                    <? } ?>
                </div>
            </td>
        </tr>
    </table>
</form>
<?php } ?>