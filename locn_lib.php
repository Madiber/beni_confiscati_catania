<?php
/**
 *  Utilities to generate locn descriptions of addresses.
 *
 * @author Cristiano Longo
 */

require("sparqllib.php");

define("PRISMA_ENDPOINT", "http://wit.istc.cnr.it:8894/sparql");

/**
 * Get the address as rdf locn:Location instance.
 */
function getLocation($street, $number, $municipality='Catania', $countryCode='IT', $countryLabel='Italy'){
  $uri=retrieveURI($street, $number, $municipality, $countryCode);
  $r="<locn:Location rdf:about=\"$uri\">\n";
  $r.=getLocnAddress($street, $number, $municipality, $countryCode, $countryLabel);
  return $r;
}

/**
 * get a string with the rdf representation of an address.
 */
function getLocnAddress($street, $number, $municipality='Catania', $countryCode='IT', $countryLabel='Italy'){
	$uri=retrieveURI($street, $number, $municipality, $countryCode)."_address";
	$r="\t<locn:address>\n";	
	$r.="\t\t<locn:Address rdf:about=\"$uri\">\n";
	$r.="\t\t\t<rdfs:label>$number, $street,  $municipality, $countryLabel</rdfs:label>\n";
	$r.="\t\t\t<locn:fullAddress>$number, $street,  $municipality, $countryLabel</locn:fullAddress>\n";
	$r.="\t\t\t<locn:thoroughfare>$street</locn:thoroughfare>\n";
	$r.="\t\t\t<locn:locatorDesignator>$number</locn:locatorDesignator>\n";
	$r.="\t\t\t<locn:postName>$municipality</locn:postName>\n";
	$r.="\t\t\t<locn:adminUnitL1>$countryCode</locn:adminUnitL1>\n";
	$r.="\t\t</locn:Address>\n";
	$r.="\t</locn:address>\n";	
	return $r;
}

/**
 * retrieve the (base) uri for the address.
 */
function retrieveURI($street, $number, $municipality='Catania', $countryCode='IT'){
	$street_no_accent=str_replace('à','a',str_replace('é','e',str_replace('ì','i',str_replace('ò','o',str_replace('ù','u',$street.$municipality)))));
	return urlencode(strtolower(str_replace(' ','_',$street_no_accent.$number.$countryCode)));
}

/**
 * Print the rdf representation of the geo point corresponding to an address.
 */
function getPoint($street, $number, $municipality='Catania', $countryCode='IT', $countryLabel='Italy'){
	$street_html=htmlentities($street);
	$address="$number, $street_html, ".htmlentities($municipality).", ".htmlentities($countryLabel);
	$request_url = "http://maps.googleapis.com/maps/api/geocode/xml?sensor=false&address=" . urlencode($address);
	$xml = simplexml_load_file($request_url) or die("url not loading");
	if ($xml->result==null || $xml->result->geometry==null || $xml->result->geometry->location==null){
		$lat=null;
		$long=null;
	} else {
		$lat = $xml->result->geometry->location->lat;
		$long = $xml->result->geometry->location->lng;
	}
	
	$location_type = $xml->result->geometry->location_type;
	if ($location_type=="APPROXIMATE") return "<!-- Unable to retrieve point for $address query=$request_url-->\n";	

	$uri=retrieveURI($street, $number, $municipality, $countryCode)."_geometry";

	$r="\t<locn:geometry>\n";
	$r.="\t\t<locn:Geometry rdf:about=\"$uri\">\n";
	if ($number==null)
	  	$r.="\t\t\t<!-- WARNING: incomplete address. Civic number missing! -->\n";
	  else
	  {
	  	$r.="\t\t\t<wsg84:lat>$lat</wsg84:lat>\n";
	  	$r.="\t\t\t<wsg84:long>$long</wsg84:long>\n";
	  }
	$r.="\t\t</locn:Geometry>\n";
	$r.="\t</locn:geometry>\n";
	return $r;
}

// stampiamo le info relative all'assegnamento e alla categoria catastale
function getInfo($street, $number, $description, $disp, $ass, $expires)
{
	$municipality='Catania';
	$countryCode='IT';
	$uri=retrieveURI($street, $number, $municipality, $countryCode)."_info";
	$r="\t<locn:info>\n";
	$r.="\t\t<locn:Info rdf:about=\"$uri\">\n";
	//$r.="\t\t\t<locn:categoria>$description</locn:categoria>\n";
	$r.="\t\t\t<locn:disponibilità>$disp</locn:disponibilità>\n";
	$r.="\t\t\t<locn:assegnatario>$ass</locn:assegnatario>\n";
	$r.="\t\t\t<locn:scadenza>$expires</locn:scadenza>\n";
	$r.="\t\t</locn:Info>\n";
	$r.="\t</locn:info>\n";
	return $r;
}

// funzione per mappare le descrizioni informali del CSV in qualcosa di più vicino alle canoniche categorie catastali.
// Per il momento, si occupa di correggere abbreviazioni e modi diversi di indicare la stessa cosa.
// Se un bene non rientra in nessuna delle categorie previste, si fa riferimento alla classe "sconosciuta".
function getCategoria($description)
{
	$description = strtolower ($description);
	switch ( $description )
	{
    case "appartam.":
        $r = "appartamento";
        break;
    case "fabbric.":
        $r = "fabbricato";
        break;
    case "terr.":
        $r = "terreno";
        break;
    case "lastrico solare":
    	$r = "lastrico_solare";
        break;
    case "terreno":
    case "villetta":
    case "giardino":
    case "bottega":
    case "magazzino":
    case "garage":
        $r = $description;
        break;
    default:
        $r = "sconosciuta";
	}	 
	return $r;
}
?>
