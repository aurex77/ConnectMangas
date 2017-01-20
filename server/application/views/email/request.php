<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1" />
    <title>Koble</title>
    <style type="text/css">
        .ReadMsgBody { width: 100%; background-color: #ffffff; }
        .ExternalClass { width: 100%; background-color: #ffffff; }
        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div { line-height: 100%; }
        html { width: 100%; }
        body { -webkit-text-size-adjust: none; -ms-text-size-adjust: none; margin: 0; padding: 0; }
        table { border-spacing: 0; border-collapse: collapse; table-layout: fixed; margin: 0 auto; }
        table table table { table-layout: auto; }
        img { display: block !important; }
        table td { border-collapse: collapse; }
        .yshortcuts a { border-bottom: none !important; }
        img:hover { opacity:0.9 !important;}
        a { color: #21b6ae; text-decoration: none; }
        .textbutton a { font-family: 'open sans', arial, sans-serif !important; color: #ffffff !important; }
        .text-link a { color: #95a5a6 !important; }
        @media only screen and (max-width: 640px) {
            body { width: auto !important; }
            table[class="table-inner"] { width: 90% !important; }
            table[class="table-full"] { width: 100% !important; text-align: center !important; }
        }
        @media only screen and (max-width: 479px) {
            body { width: auto !important; }
            table[class="table-inner"] { width: 90% !important; }
            table[class="table-full"] { width: 100% !important; text-align: center !important; }
        }
    </style>
</head>

<body>
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#494c50">
    <tr>
        <td align="center" background="cid:<?php echo $cid_background; ?>" style="background-size:cover; background-position:top;">
            <table class="table-inner" width="600" border="0" align="center" cellpadding="0" cellspacing="0">
                <tr>
                    <td height="50"></td>
                </tr>
                <tr>
                    <td height="20"></td>
                </tr>
                <!-- logo -->
                <tr>
                    <td align="center" style="line-height: 0px;">
                        <img style="display:block; line-height:0px; font-size:0px; border:0px;" src="cid:<?php echo $cid_icone; ?>" alt="logo ConnectMangas" />
                    </td>
                </tr>
                <!-- end logo -->
                <tr>
                    <td height="40"></td>
                </tr>
                <tr>
                    <td align="center">
                        <table align="center" bgcolor="#FFFFFF" style="border-radius:4px; box-shadow: 0px -3px 0px #d4d2d2;" width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td height="50"></td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <table align="center" class="table-inner" width="500" border="0" cellspacing="0" cellpadding="0">
                                        <!-- title -->
                                        <tr>
                                            <td align="center" style="font-family: 'Open Sans', Arial, sans-serif; font-size:28px; color:#3b3b3b; font-weight: bold; letter-spacing:4px;">Demande de vente ou d'échange de votre tome.</td>
                                        </tr>
                                        <!-- end title -->
                                        <tr>
                                            <td align="center">
                                                <table width="25" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td height="20" style="border-bottom:2px solid #21b6ae;"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="50"></td>
                                        </tr>
                                        <!-- content -->
                                        <tr>
                                            <td align="left" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d;">
                                                <img style="float:left; line-height:0px; font-size:0px; border:0px; height: 300px; padding-right: 15px;" src="cid:<?php echo $cid_couverture; ?>" alt="Tome" />
                                                <span>
                                                    <?php echo $username_dest; ?>, vous avez reçu une demande d'échange ou de vente pour votre tome numéro <?php echo $number; ?> de <?php echo $title; ?> de la part de l'utilisateur <?php echo $username_src; ?>.
                                                </span>
                                                <span style="display: block; margin-top: 30px;">
                                                    Vous pouvez proposer un prix de vente pour votre tome ou alors consulter la collection de <?php echo $username_src; ?> et lui proposer un échange.
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="40"></td>
                                        </tr>
                                        <tr>
                                            <td align="left" class="textbutton" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#7f8c8d;text-align:center;">
                                                <a style="background-color:#f26522;font-size:22px; color:#FFFFFF; font-weight: bold;padding: 15px 25px;" href="http://localhost:8888/connectmangas/#/profil/<?php echo $username_src; ?>">Accéder au profil de <?php echo $username_src; ?> ></a>
                                            </td>
                                        </tr>
                                        <!-- end content -->
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="60"></td>
                            </tr>

                            <!-- option -->
                            <tr>
                                <td align="center" bgcolor="#f3f3f3" style=" border-bottom-left-radius:4px; border-bottom-right-radius:4px;">
                                    <table align="center" class="table-inner" width="290" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td height="15"></td>
                                        </tr>
                                        <tr>
                                            <td class="text-link" align="center">
                                                Utilisateur :
                                            </td>
                                        </tr>
                                        <tr>
                                            <td height="15"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <!-- end option -->
                        </table>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="line-height:0px;">
                        <img style="display:block; line-height:0px; font-size:0px; border:0px;" src="https://medias.captain-repair.com/images_email/point.png" alt="img" />
                    </td>
                </tr>
                <tr>
                    <td height="30"></td>
                </tr>
                <!-- profile-img -->
                <tr>
                    <td align="center" style="line-height:0px;">
                        <a href="http://localhost:8888/connectmangas/#/profil/<?php echo $username_src; ?>">
                            <img style="display:block; line-height:0px; font-size:0px;border: 3px solid #ffffff;overflow:hidden;-webkit-border-radius:50px;-moz-border-radius:50px;border-radius:50px;width:100px;height:100px;" src="cid:<?php echo $cid_profil; ?>" alt="profil" />
                        </a>
                    </td>
                </tr>
                <!-- end profile-img -->
                <tr>
                    <td height="30"></td>
                </tr>
                <!-- social -->
                <tr>
                    <td align="center">
                        <table align="center" width="500" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td width="100%" align="center" style="line-height:0xp;" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#ffffff;">
                                    <a href="" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#ffffff;text-decoration: none;">
                                        <a href="http://localhost:8888/connectmangas/#/profil/<?php echo $username_src; ?>"><?php echo $username_src; ?></a>
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <!-- end social -->
                <tr>
                    <td height="30"></td>
                </tr>
                <!-- copyright -->
                <tr>
                    <td align="center" style="font-family: 'Open Sans', Arial, sans-serif; font-size:13px; color:#ffffff;">© 2017 ConnectMangas. Tout droits réservés.</td>
                </tr>
                <!-- end copyright -->
                <tr>
                    <td height="30"></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>

</html>