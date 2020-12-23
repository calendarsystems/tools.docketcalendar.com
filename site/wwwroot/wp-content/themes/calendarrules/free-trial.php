<?php
/* 
Template Name: free trial
*/

//custom hooks below here...

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() { ?>
      	<h1 class="entry-title">Try DocketLaw for Free.</h1>
<form action="procs/process_trial.php" method="post">

              <h3>Order Summary</h3>

               <div class="widget"><table width="100%" align="center" class="widget-wrap">             

       	         <tr>
      	              	              	          
      	              	              	          <td><p><strong>Try these four courts for 24 hours:</strong></p></td>
      	              	              	          
       	            </tr>

      	              	              	        <tr>

      	          <td><p>California Superior Court Los Angeles I/C Judge</p></td>

   	             </tr>

      	              	        <tr>

      	          <td><p>New York Supreme Court New York - Commercial Division (Hon. Bernard J. Fried - Part 60)                  </p></td>

   	             </tr>

      	              	        <tr>

      	          <td><p>US District Court, California, Central District                  </p></td>

   	             </tr>

      	              	        <tr>

      	          <td><p>USDC Southern District New York</p></td>

   	             </tr>

   	          </table></div>

              

  <h3>Create Login</h3>

               <div class="widget"><table width="100%" align="center" class="widget-wrap">             

                <tr>

                  <td width="30%"><p><strong>Username:</strong></p></td>

                  <td width="300"><label for="firstname"></label>
                    
                    <span id="sprytextfield1">
                      
                      <input name="username" id="username" autocomplete="off" type="text" size="40" />
                      
                      <span class="textfieldRequiredMsg">A value is required.</span></span>                    <div id="msgbox"></div>
                    
                  </td>

                  </tr>

                <tr>

                  <td><p><strong>Password:</strong></p></td>

                  <td><label for="lastname"></label>
                    
                    <span id="sprytextfield2">
                      
                      <input name="password" type="text" id="password" size="40" />
                      
                      <span class="textfieldRequiredMsg">A value is required.</span></span></td>

                  </tr>

                <tr>

                  <td><p><strong>Email:</strong></p></td>
                  <td><label for="email"></label>
                    <span id="sprytextfield3">
                    <input name="email" type="text" id="email" size="40" />
                    <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid format.</span></span>
                    <div id="msgbox2"></div>
                    </td>

                <tr>

                  <td nowrap="nowrap"><p><strong>Firm/Practice Name:</strong></p></td>

                  <td><label for="cvv"></label>
                    
                    <input name="firm" type="text" id="firm" size="40" /></td>

                  </tr>

              </table></div>

 <h3>Terms and Conditions</h3>
 <div class="terms">
    <?php include('include/inc_terms.php'); ?>
  </div>
                <span id="sprycheckbox1">
                <input type="checkbox" name="Agree" id="Agree" />
				<label for="Agree">Accept</label><br />
                <span class="checkboxRequiredMsg">Please agree to the terms &amp; conditions.</span></span>
<h3>Payment Information </h3>

               <div class="widget"><table width="100%" align="center" class="widget-wrap">             

                <tr>
                  <td colspan="2"><p>We don't ask for any credit card information to setup the free trial!</p></td>
                 </tr>
                <tr>

                  <td width="30%"><p><strong>First Name</strong></p></td>

                  <td><p>
                    <label for="firstname"></label>
                    
                    <span id="sprytextfield4">
                      
                    <input name="firstname" type="text" id="firstname" size="50" />
                      
                    <span class="textfieldRequiredMsg">A value is required.</span></span></p></td>

                 </tr>

                <tr>

                  <td width="30%"><p><strong>Last Name</strong></p></td>

                  <td><p>
                    <label for="lastname"></label>
                    
                    <span id="sprytextfield5">
                      
                    <input name="lastname" type="text" id="lastname" size="50" />
                      
                    <span class="textfieldRequiredMsg">A value is required.</span></span></p></td>

                 </tr>

                <tr>
                  
                  <td width="30%" nowrap="nowrap">&nbsp;</td>
                  
                  <td align="right"><label for="cvv"></label>                    <input type="image" name="imageField" id="imageField" src="assets/images/btn_submit.png" /></td>
                  
                </tr>

              </table></div>

              <input name="stateIDs" type="hidden" value="" />

              <input name="courtIDs" type="hidden" value="-14768,-44295,-14253,-14328," />

              </form>
<script type="text/javascript">

var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "none", {validateOn:["blur"]});

var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "none", {validateOn:["blur"]});

var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "email", {validateOn:["blur"]});

var sprytextfield4 = new Spry.Widget.ValidationTextField("sprytextfield4", "none", {validateOn:["blur"]});

var sprytextfield5 = new Spry.Widget.ValidationTextField("sprytextfield5", "none", {validateOn:["blur"]});

var sprycheckbox1 = new Spry.Widget.ValidationCheckbox("sprycheckbox1", {validateOn:["blur"]});

</script>

<?php }

genesis();
?>