<?php
	require("business/devis.php");
	require("business/tva.php");
	require("inc.php");
	
	// Suppression devis
	if (isset($_GET['devis_sup'])) {
		$devis = new Devis();
		$devis->findById($_GET['devis_sup']);
		$devis->delete();
	}
	
	// Analyse parametres
	$societe="";
	if (isset($_GET['societe'])) {
		$societe=$_GET['societe'];
	}
	$hidd="";
	if (isset($_GET['hidd'])) {
		$hidd=$_GET['hidd'];
	}
	$interlocuteur="";
	if (isset($_GET['interlocuteur'])) {
		$interlocuteur=$_GET['interlocuteur'];
	}
	$validation="";
	if (isset($_GET['validation'])) {
		$validation=$_GET['validation'];
	}
	$date1="";
	if (Isset($_GET['date1'])) {
		$date1=$_GET['date1'];
	}
	$date11=implode('-',array_reverse(explode('/',$date1)));
	$date2="";
	if (isset($_GET['date2'])) {
		$date2=$_GET['date2'];
	}
	$date22=implode('-',array_reverse(explode('/',$date2)));
	$sort="";
	if (isset($_GET['sort'])) {
		$sort=$_GET['sort'];
	}
	if(empty($sort)){
		$sort="DATE_EMISSION DESC, _ID DESC";
	}

	// Pagination
	$pag="";
	$pagee="";
	$page="0";
	if (isset($_GET['page'])) {
		$page=$_GET['page'];
	}
	if(empty($page)){
		$page="0";
	}

	$nombre="100";
	if (isset($_GET['nombre'])) {
		$nombre=$_GET['nombre'];
	}
	if (empty($nombre)) {
		$nombre="100";
	}
	$url="?interlocuteur=$interlocuteur&date1=$date1&date2=$date2&societe=$societe";

	// Edition CSV du devis
	if (isset($_GET['print'])) {
		header("Content-type: application/vnd.ms-excel");
		$inputFile = $societeContact->societe->id . "_devis.csv";
		header("Content-disposition: attachment; filename=$inputFile");
		$allTva = Tva::findAll();
		
		$csv = "SUIVI PAR;REFERENCE;DESTINATAIRE;DATE EMISSION; OBJET;MONTANT HT";
		foreach ($allTva as $tva) {
			$csv .= ";MONTANT " . $tva->libelle;
		}
		$csv .= ";MONTANT TTC;REMISE;ACOMPTE;". utf8_decode('passé en facture') .";COMMENTAIRES\n";
		
		$arr = array();
		
		$quete = " AND ";
		//$quete="WHERE ";
		if (!empty($interlocuteur)) {
			$nom="ID_SOCIETE_CONTACT= $interlocuteur";
			array_push ($arr, $nom);
		}
		if (!empty($societe)) {
			$nom2="ID_SOCIETE_CLIENT= $societe";
			array_push ($arr, $nom2);
		}
		if (!empty($validation)) {
			$valid="VALIDATION= 0";
			array_push ($arr, $valid);
		}
		if (!empty($date1)) {
			$prenom="DATE_EMISSION >= '$date11'";
			array_push ($arr, $prenom);
		}
		if (!empty($date2)) {
			$societe="DATE_EMISSION <= '$date22'";
			array_push ($arr, $societe);
		}
		$count=count($arr);
		if (!empty($count)) {
			for ($i=0; $i<$count ; $i++) {
				if (!empty($i)) {
					$quete=$quete." AND ";
				}
				$quete=$quete.$arr[$i];
			}
			// $quete=$quete.$arr[$count];
		}
		else {
			$quete="";
		}

		$select = "select * from devis WHERE ID_SOCIETE = " . $societeContact->societe->id . " " . $quete;
		$result = mysql_query($select);
		while ($val=mysql_fetch_array($result)) {
			if(empty($val['VALIDATION'])){
				$valVALIDATION="NON";
			}
			else{
				$valVALIDATION="OUI";
			}
			$interloc="select INITIALE from societe_contact where _ID='" . $val['ID_SOCIETE_CONTACT'] . "'";
			$interloc1=mysql_query($interloc);
			$interloc2=mysql_fetch_array($interloc1);
			$destinat1=mysql_query("select ENTREPRISE, NOM from societe_client where _ID='" . $val['ID_SOCIETE_CLIENT'] . "'");
			$destinat2=mysql_fetch_array($destinat1);
			$destinat=$destinat2['ENTREPRISE']." - ".$destinat2['NOM'];

			$str = str_replace(CHR(10)," ",$val['COMMENTAIRE']);
			$str = str_replace(CHR(13)," ",$str);

			$titre = str_replace(CHR(10)," ",$val["TITRE"]);
			$titre = str_replace(CHR(13)," ",$titre);
			
			$devisEdit = new Devis();
			$devisEdit->findById($val['_ID']);

			$csv .= utf8_decode($interloc2["INITIALE"]).';'. utf8_decode($val["REFERENCE"]).';'. utf8_decode($destinat).';'. utf8_decode($val["DATE_EMISSION"]).';'. utf8_decode($titre).';'. $devisEdit->getHTPriceFormat(); // le \n final entre " " 
			foreach ($allTva as $tva) {
				$csv .= ';'.$devisEdit->getTvaPriceFormat($tva->id);
			}
			$csv .= ';'.$devisEdit->getTTCPriceFormat().';'.$devisEdit->getTotalRemiseFormat().';'.$devisEdit->getAcompteFormat().';'. utf8_decode($val["VALIDATION"]).';'. utf8_decode($str)."\n";
			// le \n final entre " "
		}
		print($csv);
		exit;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US" xml:lang="en">
<head>
    <!--
    Created by Artisteer v2.4.0.25435
    Base template (without user's data) checked by http://validator.w3.org : "This page is valid XHTML 1.0 Transitional"
    -->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
    <title>CAP BOX</title>

    <link rel="stylesheet" href="style.css" type="text/css"  />
    <!--[if IE 6]><link rel="stylesheet" href="style.ie6.css" type="text/css"  /><![endif]-->
    <!--[if IE 7]><link rel="stylesheet" href="style.ie7.css" type="text/css"  /><![endif]-->

    <script type="text/javascript" src="script.js"></script>
    <script type="text/javascript">

	function ex(devis)
	{
		var x=confirm("Etes-vous sûr de supprimer ce devis ?")
		if (x)
		 window.location="devis_liste.php?devis_sup="+devis;
	}
	function tex(devis)
	{
		var x=confirm("Etes-vous sûr de transférer ce devis en facture ? \n Il ne sera plus modifiable...")
		if (x)
		 window.location="facture.php?transforme=1&id="+devis;
	}
	function zex(devis)
	{
		var x=confirm("Ce devis est vérouillé car il a été passé en facture. \n Voulez-vous malgré tout le dévérouiller ?")
		if (x)
		 window.location="devis.php?id="+devis;
	}
	function go()
	{
		window.location=document.getElementById("menu").value;
	}
	</script>
    <style type="text/css">
		<!--
		.Style1 {color: #FFFFFF}
		.Style4 {font-size: 85%}
		.Style6 {font-size: 85%; font-weight: bold; }
		.Style7 {font-size: 85%; font-weight: bold; color: #FFFFFF; }
		-->
    </style>
</head>
<body>
	<div id="art-page-background-simple-gradient">
        <div id="art-page-background-gradient"></div>
    </div>
    <div id="art-main">
        <div class="art-sheet">
            <div class="art-sheet-tl"></div>
            <div class="art-sheet-tr"></div>
            <div class="art-sheet-bl"></div>
            <div class="art-sheet-br"></div>
            <div class="art-sheet-tc"></div>
            <div class="art-sheet-bc"></div>
            <div class="art-sheet-cl"></div>
            <div class="art-sheet-cr"></div>
            <div class="art-sheet-cc"></div>
            <div class="art-sheet-body">
                <div class="art-header">
                    <div class="art-header-png"></div>
                </div>
                <?php require("topmenu.php"); ?>

                <div class="art-content-layout">
                    <div class="art-content-layout-row">
                        <div class="art-layout-cell art-content">
                        <div class="art-post">
                                <div class="art-post-tl"></div>
                                <div class="art-post-tr"></div>
                                <div class="art-post-bl"></div>
                                <div class="art-post-br"></div>
                                <div class="art-post-tc"></div>
                                <div class="art-post-bc"></div>
                                <div class="art-post-cl"></div>
                                <div class="art-post-cr"></div>
                                <div class="art-post-cc"></div>
                                <div class="art-post-body">
                                  <div class="art-post-inner art-article">
                                            <h2 class="art-postheader">
                                                <img src="images/postheadericon.png" width="26" height="26" alt="postheadericon" />
                                                Liste de vos devis</h2>
                                            <div class="art-postcontent">
                                                <!-- article-content -->



                                                  <table width="100%" border="0" cellpadding="0" cellspacing="0" class="art-article">
                                                  <tbody>
                                                    <?php
													require("turl.php");
													?><form action="" method="get" name="form1">
                                                    <tr>
                                                      <td colspan="9"><table width="100%" cellpadding="0" cellspacing="0">
      <tr>
        <td height="32" background="images/fd_nav_ss_menu.png" bgcolor="#F0F0F0">
        	<span style="font-weight: bold">
        		<a href="devis.php"><img src="images/icones/ajouter-une-page-blanche-icone-9840-32.png" alt="" width="24" height="24" align="absmiddle" />Créer un nouveau devis </a>
        		<img src="images/menuseparator.png" width="1" height="24" align="absmiddle" />
				<a href="<?php echo "$url&print=1"; ?>"><img src="images/icones/page-excel-icone-6057-32.png" width="24" height="24" align="absmiddle" /> Export</a>
				<img src="images/menuseparator.png" width="1" height="24" align="absmiddle" />
				<?php if(empty($validation)){ ?>
					<a href="<?php echo"$url&page=$pag&nombre=$nombre&validation=VALIDATION"; ?>"><img src="images/icones/temps-qui-passe-icone-6367-32.png" width="24" height="24" align="absmiddle" /> Voir les devis en attente de validation</a>
				<?php }else{ ?>
					<a href="<?php echo"$url&page=$pag&nombre=$nombre&validation="; ?>"><img src="images/icones/temps-qui-passe-icone-6367-32.png" width="24" height="24" align="absmiddle" /> Voir tous les devis</a>
				<?php } ?>
			</span>
		</td>
      </tr>
      <tr>
        <td bgcolor="#F9F9F9"><!-- Periode de recherche -->

            <div>
              <p><strong>Suivi par
                  <select name="interlocuteur" id="interlocuteur">
                  <option value=""></option>
                  <?php
					  $iselect = mysql_query("select DISTINCT soco.INITIALE, soco._ID FROM devis JOIN societe_contact soco WHERE devis.ID_SOCIETE = " . $societeContact->societe->id . " AND soco.ID_SOCIETE = " . $societeContact->societe->id . " ");
					  while($iresul = mysql_fetch_array($iselect)) {
						  if (isset($_GET['interlocuteur']) && $_GET['interlocuteur'] == $iresul['_ID']) {
							  $selected =" selected";
						  }
						  else {
							  $selected="";
						  }
						  echo"<option value=\"".$iresul['_ID']."\" $selected>".$iresul['INITIALE']."</option>";
					  }
				  ?>
                  </select>
Date de début</strong>:
<input name="date1" type="text" id="date1" title="Sélectionner une date de début de période en cliquant sur cette zone" value="<?php echo $date1; ?>" size="10" alt="date" />
<strong>Date de fin</strong>:
<input name="date2" type="text" id="date2" title="Sélectionner une date de fin de période en cliquant sur cette zone" value="<?php echo $date2; ?>" size="10" alt="date" />
     <strong> &nbsp;Client </strong>:
     <select name="societe" id="societe">
                  <option value=""></option>
                  <?php
					  $sselect = mysql_query("select * FROM societe_client WHERE ID_SOCIETE = " . $societeContact->societe->id . " ");
					  while($sresul=mysql_fetch_array($sselect)) {
						  if (isset($_GET['societe']) && $_GET['societe']==$sresul['_ID']) {
							  $selected =" selected";
						  }
						  else {
							  $selected="";
						  }
	                      echo"<option value=\"".$sresul['_ID']."\" $selected>".$sresul['NOM']." - ".$sresul['ENTREPRISE']."</option>";
					  }
				  ?>
                  </select>
 <a href="#" onclick="document.forms['form1'].submit();"><img src="images/icones/loupe-icone-4171-32.png" width="32" height="32" align="absmiddle" /> <span style="font-weight: bold">Recherche </span></a><a href="devis_liste.php"><img src="images/icones/arrow-rotation-anti-horaire-icone-4507-32.png" width="32" height="32" align="absmiddle" /><span style="font-weight: bold">Réinitialiser</span></a></p>
            </div>
          <!-- /Periode de recherche -->
            <!-- /Periode de recherche -->        </td>
      </tr>
      <!--startprint-->

      <tr>
        <td><!-- Liste des pieces -->
            <div id="listePiece">
              <!-- Confirmation Piece -->

              <!-- /Confirmation Piece -->
              <center id="tableauListe">
                <!-- Pagination -->
                <table width="100%">
                      <!-- Entete du tableau -->

                      <tbody>

                        <tr>
                          <th width="38" align="center" bgcolor="#5289BA"><span class="Style1" style="font-size: 11px">Suivi</span></th>
                          <th width="75" align="center" bgcolor="#5289BA"><span class="Style1" style="font-size: 11px"> Date </span></th>
                          <th width="85" bgcolor="#5289BA"><span class="Style1" style="font-size: 11px"> Réf. </span></th>
                          <th width="188" bgcolor="#5289BA"><span class="Style1" style="font-size: 11px"> Client </span></th>
                          <th width="254" bgcolor="#5289BA"><span class="Style1" style="font-size: 11px"> Objet </span></th>
                          <th width="82" bgcolor="#5289BA"><span class="Style1" style="font-size: 11px"> Montant HT</span></th>
                          <!--th class="data-table">                    Acompte                </th-->
                          <th width="152" bgcolor="#5289BA"><span class="Style1" style="font-size: 11px"> Actions</span></th>
                        </tr>
                        <!-- /Entete du tableau -->
                        <!-- Corps du tableau -->
                        <tr>
                          <td bgcolor="#CDDDEB"><div align="center"><a href="<?php echo"$url&page=$pag&nombre=$nombre&sort=ID_SOCIETE_CONTACT "; ?>"><img src="images/icones/icon-max.gif" width="17" height="16" align="absmiddle" /></a><a href="<?php echo"$url&page=$pag&nombre=$nombre&sort=ID_SOCIETE_CONTACT DESC"; ?>"><img src="images/icones/icon-min.gif" width="17" height="16" align="absmiddle" /></a></div></td>
                          <td bgcolor="#CDDDEB"><div align="center"><a href="<?php echo"$url&page=$pag&nombre=$nombre&sort=DATE_EMISSION "; ?>"><img src="images/icones/icon-max.gif" width="17" height="16" align="absmiddle" /></a><a href="<?php echo"$url&page=$pag&nombre=$nombre&sort=DATE_EMISSION DESC"; ?>"><img src="images/icones/icon-min.gif" width="17" height="16" align="absmiddle" /></a></div></td>
                          <td bgcolor="#CDDDEB"><div align="center"><a href="<?php echo"$url&page=$pag&nombre=$nombre&sort=REFERENCE"; ?>"><img src="images/icones/icon-max.gif" width="17" height="16" align="absmiddle" /></a><a href="<?php echo"$url&page=$pag&nombre=$nombre&sort=REFERENCE DESC"; ?>"><img src="images/icones/icon-min.gif" width="17" height="16" align="absmiddle" /></a></div></td>
                          <td bgcolor="#CDDDEB"><!--<div align="center"><a href="<?php //echo"$url&page=$pag&nombre=$nombre&sort=DESTINATAIRE"; ?>"><img src="images/icones/icon-max.gif" width="17" height="16" align="absmiddle" /></a><a href="<?php //echo"$url&page=$pag&nombre=$nombre&sort=DESTINATAIRE DESC"; ?>"><img src="images/icones/icon-min.gif" width="17" height="16" align="absmiddle" /></a></div>--></td>
                          <td bgcolor="#CDDDEB"><div align="center"><a href="<?php echo"$url&page=$pag&nombre=$nombre&sort=TITRE"; ?>"><img src="images/icones/icon-max.gif" width="17" height="16" align="absmiddle" /></a><a href="<?php echo"$url&page=$pag&nombre=$nombre&sort=TITRE DESC"; ?>"><img src="images/icones/icon-min.gif" width="17" height="16" align="absmiddle" /></a></div></td>
                          <td bgcolor="#CDDDEB"><div align="center"><a href="<?php echo"$url&page=$pag&nombre=$nombre&sort=TOTAL_HT"; ?>"><img src="images/icones/icon-max.gif" width="17" height="16" align="absmiddle" /></a><a href="<?php echo"$url&page=$pag&nombre=$nombre&sort=TOTAL_HT DESC"; ?>"><img src="images/icones/icon-min.gif" width="17" height="16" align="absmiddle" /></a></div></td>
                          <td align="left" bgcolor="#CDDDEB">&nbsp;</td>
                        </tr>

                        <?php
							//tri
							$arr=array();
							$quete=" AND ";
							//$quete="WHERE ";
							if (!empty($interlocuteur)) {
								$nom="INTERLOCUTEUR= $interlocuteur";
								array_push ($arr, $nom);
							}
							if (!empty($societe)) {
								$nom2="DESTINATAIRE= $societe";
								array_push ($arr, $nom2);
							}
							if (!empty($validation)) {
								$valid="VALIDATION= 0";
								array_push ($arr, $valid);
							}
							if (!empty($date1)) {
								$prenom="DATE_EMISSION >= '$date11'";
								array_push ($arr, $prenom);
							}
							if (!empty($date2)) {
								$societe="DATE_EMISSION <= '$date22'";
								array_push ($arr, $societe);
							}
							$count=count($arr);
							if (!empty($count)) {
								for ($i=0; $i<$count ; $i++) {
									if (!empty($i)) {
										$quete = $quete . " AND ";
									}
									$quete = $quete . $arr[$i];
								}
								// $quete = $quete . $arr[$count];
							}
							else {
								$quete = "";
							}

							$parametres="order by ".$sort. " LIMIT ".$page*$nombre. ",".$nombre;
							$select=mysql_query("select _ID from devis WHERE ID_SOCIETE = " . $societeContact->societe->id . " " . $quete . " " . $parametres);
							$selecti=mysql_query("select * from devis WHERE ID_SOCIETE = " . $societeContact->societe->id . " " . $quete);
							$totp=mysql_num_rows($selecti);
							$toggle="";
							$totalpage=0.0;
							while($fetch=mysql_fetch_array($select)) {
								$devis = new Devis();
								$devis->findById($fetch['_ID']);
								if($toggle&1) {
									$bgcolor="#EAF0F7";
								}
								else {
									$bgcolor="#F4F8FB";
								}
						?>
                        <tr bgcolor="<?php echo $bgcolor; ?>">
                          <td ><div align="center"><span style="font-size: 11px">
                          <?php
								$interloc = "select INITIALE from societe_contact where _ID=" . $devis->idSocieteContact;
								$interloc1=mysql_query($interloc);
								$interloc2=mysql_fetch_array($interloc1);
								echo $interloc2['INITIALE'];
						  ?>
                          </span></div></td>
                          <td ><span style="font-size: 11px"> <?php $d = $devis->dateEmission; echo date("d/m/Y",strtotime($d)); ?> </span></td>
                          <td ><span style="font-size: 11px"> <?php echo $devis->reference; ?>  </span></td>
                          <!-- Tier  -->
                          <td align="left" ><span style="font-size: 11px">
                          <?php
								  $requet = "select * from societe_client where _ID='" . $devis->idSocieteClient . "'";
								  $resul = mysql_query($requet);
								  $contact = mysql_fetch_array($resul);
								  echo $contact['CIVILITE']." ".mb_strtoupper($contact['NOM'],'UTF-8')." - ".mb_strtoupper($contact['ENTREPRISE'],'UTF-8');
						  ?>
                          </span></td>
                          <td align="left" > <?php echo $devis->titre; ?></td>
                          <td align="right" ><span style="font-size: 11px">
						  <?php
									echo $devis->getHTPriceRemiseFormat();
						  ?> </span></td>
                          <td align="left" ><div align="center" style="font-size: 11px">
						  <?php
								if(empty($devis->validation)) {
						  ?>
                          <a href="devis.php?id=<?php echo $devis->id; ?>"><img src="images/icones/editer_16.png" alt="Modifier" name="imgModifier2" width="16" height="16" id="imgModifier3" title="Modifier" /></a>&nbsp;
						  <?php
						  		}
						  		else{
						  ?>
						  <a href="#" onclick="zex(<?php echo $devis->id; ?>);">
                          <img alt="déjà transféré" src="images/icones/cadenas-ouvert-icone-4601-16.png" title="déjà transféré" width="16" height="16" /></a>&nbsp;
						  <?php
						  		}
						  		if(empty($devis->validation)) {
						  ?>
						  <a href="#" onclick="tex(<?php echo $devis->id; ?>);"><img src="images/icones/pieces-de-monnaie-fleche-icone-6896-16.png" alt="Transférer en facturation" name="imgTransferer3" width="16" height="16" id="imgTransferer3" title="Transférer en facturation" /></a>&nbsp;
						  <?php
						  		}else{
						  ?>
                          <img alt="déjà transféré" src="images/icones/tick-icone-8838-32.png" title="déjà transféré" width="16" height="16" />&nbsp;
						  <?php
						  		}
						  ?>
                          <a href="devis.php?dup=<?php echo $devis->id; ?>"><img alt="Recopier" id="imgRecopie3" src="images/icones/copie_16.png" title="Recopier" width="16" height="16" /></a>&nbsp;&nbsp;<a href="devis_pdf.php?id=<?php echo $devis->id; ?>" target="_blank"><img alt="Imprimer" id="imgPDF3" src="images/icones/imprimante-icone-5571-16.png" title="Imprimer" width="16" height="16" /></a> &nbsp;&nbsp;<a href="#" onclick="ex(<?php echo $devis->id; ?>);"><img alt="Supprimer" id="imgSupprimer3"  src="images/icones/supprimer_16.png" title="Supprimer" width="16" height="16" /></a></div></td>
                        </tr>
                        <?php
								$toggle++;
								$totalpage = $totalpage + $devis->getHTPriceRemise();
							 }
						?>
                        <!-- total general du mois -->
                        <!-- total general de la periode -->
                        <tr>
                          <td height="2" colspan="8" bgcolor="#5289BA"></td>
                          </tr>
                        <tr>
                          <td colspan="4">   </td>
                          <td align="right"> TOTAL PAGE </td>
                          <td align="right" bgcolor="#5289BA"><span class="Style1"><strong><?php echo number_format($totalpage, 2, ',', ' '); ?></strong></span> </td>
                          <td colspan="2">   </td>
                        </tr>
                      </tbody>
                    </table>
              </center>
            </div></td>
      </tr>

    </table></td>
                                                    </tr>
                                                    <tr>
                                                      <td colspan="8"><div align="right"></div></td>
                                                    </tr>
                                                    <tr>
                                                      <td colspan="8"><p align="center">Nombre d'articles par page
                                                      <select name="select5" id="menu" onchange="go()">
                                                              <option value="<?php echo "$url&page=$pag&nombre=10&sort=$sort"; ?>"<?php if($nombre==10){ echo "selected=\"selected\""; }?>>10</option>
                                                              <option value="<?php echo "$url&page=$pag&nombre=50&sort=$sort"; ?>"<?php if($nombre==50){ echo "selected=\"selected\""; }?>>50</option>
                                                              <option value="<?php echo "$url&page=$pag&nombre=100&sort=$sort"; ?>" <?php if($nombre==100){ echo "selected=\"selected\""; }?>>100</option>
                                                        </select>
                                                      </p>
                                                      <p align="center">
                                                      <?php
													  		$pag=$page-1;
													  		$pagee=$page+1;
													  		$totpages=ceil($totp/$nombre);
															
													  		if($pag>=0) {?><a href="<?php echo"$url&page=$pag&nombre=$nombre&sort=$sort"; ?>">Précédent</a> |<?php } ?> Page <?php echo $pagee; ?> sur <?php echo $totpages; if($pagee<$totpages){?> | <a href="<?php echo"$url&page=$pagee&nombre=$nombre&sort=$sort"; ?>">Suivant</a><?php } ?></p></td>
                                                    </tr></form>
                                              </tbody></table>

                                                	<!--endprint-->

                                              <!-- /article-content -->
                                            </div>
                                            <div class="cleared"></div>
                                  </div>
                                  <div class="cleared"></div>

                            		<div class="cleared"></div>
                          </div></div>
                        </div>
                  </div>
                </div>
                <div class="cleared"></div><div class="art-footer">
                    <div class="art-footer-inner">
                      <div class="art-footer-text">
                            <p><a href="#">Nous contacter</a> | <a href="#">Conditions d'utilisation</a> | <a href="#">Mentions légales</a>
                                | <br />
                                Copyright &copy; 2010 - Tous droits réservés</p>
                      </div>
                  </div>
                    <div class="art-footer-background"></div>
                </div>
        		<div class="cleared"></div>
            </div>
        </div>
        <div class="cleared"></div>
        <p class="art-page-footer"></p>
    </div>
</body>
</html>
