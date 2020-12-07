

<?php
/* 
Template Name: update password
*/

//custom hooks below here...

remove_action('genesis_loop', 'genesis_do_loop');
add_action('genesis_loop', 'custom_loop');

function custom_loop() {

?>


      	<?php if ($_GET['cardfailed'] == 1){ ?>

        <?php }else{ ?>
        <h1 class="entry-title">Retrieve Password</h1>
        <?php } ?>
                <h3>Retrieve Password</h3>
               <div class="widget"><table width="100%" align="center" class="widget-wrap">             
                <tr>
                  <td><p><strong>Email address:</strong></p></td>
                  <td><label for="email"></label>
                    <span id="sprytextfield3">
                      <input name="email" type="email" id="email" size="40" />
                      <span class="textfieldRequiredMsg">A value is required.</span></span></td>
                  </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td align="right"><input name="imageField" type="image" id="imageField" src="assets/images/btn_submit.png" /></td>
                  </tr>
              </table>
             
              </form>
              </div>
<script type="text/javascript">
var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "none", {validateOn:["blur"]});
</script>
<?php
mysqli_free_result($userinfo);

mysqli_free_result($States);

mysqli_free_result($Courts);

mysqli_free_result($SelectedState);

mysqli_free_result($cart);

mysqli_free_result($stateComments);


}
genesis();
?>
