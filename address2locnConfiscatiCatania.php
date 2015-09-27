<?php
require("locn_lib.php");

/**
 * Con una sola lettura del file CSV, genera tre RDF:
 *   - uno con le descrizioni dei beni confiscati;
 *   - uno con i dati sulla geolocalizzazione di Google;
 *   - uno con entrambi.
 *
 * @author Cristiano Longo
 * @author Mario Alvise Di Bernardo
 */

//Inizializziamo le variabili per generare i due file in output
$str_beni = "";
$str_geo = "";

// Iniziamo la lettura
$handle=fopen('php://stdin', 'r');
//$handle=fopen('elenco-beni-confiscati-mafia.csv', 'r');

$str_beni .= "<rdf:RDF xml:base=\"http://www.comune.catania.it/amministrazione-trasparente/beni-immobili-e-gestione-del-patrimonio/patrimonio-immobiliare.aspx\"\n";
$str_beni .=  "\txmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\"\n";
$str_beni .=  "\txmlns:rdfs=\"http://www.w3.org/2000/01/rdf-schema#\"\n";
$str_beni .=  "\txmlns:wsg84=\"http://www.w3.org/2003/01/geo/wgs84_pos#\"\n";
$str_beni .=  "\txmlns:locn=\"http://www.w3.org/ns/locn#\"\n";
$str_beni .=  "\txmlns:dcterms=\"http://purl.org/dc/terms/\"\n";
$str_beni .=  "\txmlns:owl=\"http://www.w3.org/2002/07/owl#\">\n";
$str_beni .=  "<owl:Ontology rdf:about=\"http://www.comune.catania.it/amministrazione-trasparente/beni-immobili-e-gestione-del-patrimonio/patrimonio-immobiliare.aspx\">\n";
$str_beni .=  "\t<rdfs:comment>RDF representation of confiscated assets in the municipality of Catania, retrieved from the open data catalogue of this municipality</rdfs:comment>\n";
$str_beni .=  "</owl:Ontology>\n";
$str_beni .=  "<!-- Generated by address2locnConfiscatiCatania.php -->\n";

$str_geo .= $str_beni;
echo $str_beni;

$count=0;
$ignored=0;
//avviamo una prima lettura, in modo da escludere la prima riga contenente i titoli delle colonne
$row = fgetcsv($handle,255,";");
while( $row = fgetcsv($handle,255,";") )
{
	// i dati commentati saranno omessi
	$street=$row[0];		//Indirizzo
	$number=$row[1];		//Civico
	$description=$row[2];	//Tipologia
	$notes=$row[4];			//Note
	//$sqm=$row[3];			//mq
	//$decmin=$row[5];		//Decreto Min. n°
	//$data1=$row[6];			//Data
	$idL1=$row[7];			//Foglio
	$idL2=$row[8];			//Particella
	$idL3=$row[9];			//Sub
	$disp=$row[10];			//Disponibilità
	$ass=$row[11];			//Associazione Assegnataria	
	//$det_dir=$row[12];		//Determina Dirigenziale n.
	//$data2=$row[13];		//Data
	$expires=$row[14];		//Data Scadenza
	//$trascr=$row[15];		//Trascr.

	if ($idL1==='' || $idL2==='')
	{
		$url="unrecognizable$ignored";
		$ignored++;
	}
	else
	{
		$url="$idL1-$idL2-$idL3";
		$count++;
	}
	$tmp = "<owl:Thing rdf:about=\"$url\">\n";
	$categoria = getCategoria($description);
	$tmp .= "<rdfs:type rdf:resource=\"http://comune.catania.it/classificazionebeni/$categoria\"/>\n";
	$str_beni .= $tmp;
	$tmp2 = "<owl:Thing>\n";
	echo $tmp;

	if (isset($description))
	{
		$descriptionUTF8=utf8_encode($description);
		if ($descriptionUTF8==$description)
		{
			$tmp = "<rdfs:label>$descriptionUTF8, $notes</rdfs:label>\n";
			$str_beni .= $tmp;
			echo $tmp;
		}
		else
		{
			$tmp = "<rdfs:label>u\nrecognizable asset</rdfs:label>\n";
			$str_beni .= $tmp;
			echo $tmp;
		}
	}
	// stampiamo le info relative all'assegnamento e alla categoria catastale 
	$tmp = getInfo($street, $number, $description, $disp, $ass, $expires);
	$str_beni .= $tmp;
	//echo $tmp;

	// estraiamo i dati relativi alla geolocalizzazione...
	$tmp2 .= getPoint($street, $number, "Catania");

	// ... e quelli relativi all'indirizzo.
	$tmp = "<locn:location>\n";
	$tmp .= getLocation($street, $number, "Catania");
	echo $tmp2;
	$tmp .="</locn:Location>\n";
	$tmp .=  "</locn:location>\n";
	$str_beni .= $tmp;
	echo $tmp;

	

	$tmp = "</owl:Thing>\n";
	$str_beni .= $tmp;
	$tmp2 .= $tmp;
	echo $tmp;
	if ($street != null && $number != null)
		$str_geo .= $tmp2;
}
echo "<!-- found $count locations -->\n";
echo "<!-- ignored $ignored -->\n";
$tmp = "</rdf:RDF>\n";
$str_beni .= $tmp;
$str_geo .= $tmp;
echo $tmp;
fclose($handle);

// Infine generiamo i due file in output
$file1 = 'BeniConfiscati.rdf';
$file2 = 'BeniConfiscati_Geo.rdf';
file_put_contents($file1, $str_beni);
file_put_contents($file2, $str_geo);
?>