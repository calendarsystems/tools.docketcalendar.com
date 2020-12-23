<link href="Styles/Site.css" rel="stylesheet" type="text/css" /><link rel="shortcut icon" href="images/favicon.ico" />

    <style type="text/css">

        .style1

        {

            height: 352px;

            width: 469px;

        }

    </style>

</head>

<!-- Date Calculator Javascript -->

<script language='javascript' type='text/javascript'>

    document.newGetElementById = document.getElementById;

    document.getElementById = function (sElementID) {

        var oFirstTry;



        oFirstTry = document.newGetElementById(sElementID);

        if (oFirstTry)

            return oFirstTry;

        else

            return document.newGetElementById('MainContent_' + sElementID);

    }



    function ResetTerms() {

        document.getElementById('btnCalculate').disabled = !document.getElementById('chkTerms').checked;

    }



    function clearForm() {

        var now = new Date();

        document.getElementById('txtTriggerDate').value = now.getMonth() + 1 + '/' + now.getDate() + '/' + now.getFullYear();

        document.getElementById('txtCalculatedDate').value = "";

        document.getElementById('txtUnitCount').value = "";

        document.getElementById('cmbJurisdictions').selectedIndex = 0;

        document.getElementById('rbUnits_0').checked = true;

        document.getElementById('rbDirection_0').checked = true;

        document.getElementById('rbRollDirection_0').checked = true;

    }



    function IsDate(sDate) {

        var scratch = new Date(sDate);

        if (scratch.toString() == "NaN" || scratch.toString() == "Invalid Date") {

            return false;

        }

        else {

            return true;

        }

    }



    function IsNumeric(strString)

    //  check for valid numeric strings	

    {

        var strValidChars = "0123456789";

        var strChar;

        var blnResult = true;



        if (strString.length == 0) return false;



        //  test strString consists of valid characters listed above

        for (i = 0; i < strString.length && blnResult == true; i++) {

            strChar = strString.charAt(i);

            if (strValidChars.indexOf(strChar) == -1) {

                blnResult = false;

            }

        }

        return blnResult;

    }



    function OnChangeEvent(unit) {

        switch (unit) {

            case 'txtTriggerDate':

                if (!IsDate(document.getElementById('txtTriggerDate').value)) {

                    alert("Invalid date.")

                    document.getElementById('txtTriggerDate').value = ''

                    return;

                }

                break;

            case 'txtUnitCount':

                if (!IsNumeric(document.getElementById('txtUnitCount').value)) {

                    alert("Supplied value must be numeric.")

                    document.getElementById('txtUnitCount').value = ''

                    return;

                }

                

                break;

            default:

                alert('default')

        }

    }



    function UltraWebTree1_NodeChecked(treeId, nodeId, bChecked) {

        var selectedNode = igtree_getNodeById(nodeId);

        var parentNode = igtree_getNodeById(nodeId).getParent();

        var childNodes = selectedNode.getChildNodes();



        if (bChecked == true) {

            for (n in childNodes) {

                childNodes[n].setChecked(bChecked);

            }

        }



        if (bChecked == false && parentNode != null){

            parentNode.setChecked(bChecked)

         //   UltraWebTree1_NodeChecked(treeId, parentNode, bChecked)

        }

    }



    function OnMouseOverLogin() {

        document.getElementById('HeadLoginView_LoginButton').src = "images/login_roll.jpg";

    }



    function OnMouseOut() {

        document.getElementById('HeadLoginView_LoginButton').src = "images/login.jpg";

    }



    function ShowLoginPage() {

        window.showModalDialog('http://www.calendarrulesonline.com/uat/Login.aspx', 'Arg1', 'dialogHeight: 170px; dialogWidth: 335px; dialogTop: 100px; dialogLeft: 780px; edge: Raised; center: No; help: No; scroll: No; status: Yes;');

    }





</script> 


<table><tr><td><?php session_start();//echo $_SERVER["SCRIPT_NAME"]; ?> </td></tr></table>


<div class="main"><?php echo $_SESSION['firstname'];?> - <?php echo $_COOKIE['userid'];?>
<?php if ($_SESSION['firstname'] <> ''){ ?>
<a style="float:right;" href="<?php //echo $SSLDomain; ?>/dev/logout.php">Logout</a> 
<?php }else{ ?>
<a style="float:right;" href="<?php //echo $SSLDomain; ?>/login">Manage Account</a>
<?php } ?>