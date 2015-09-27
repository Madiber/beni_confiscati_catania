# Open Data: una mappa dei beni confiscati alla mafia del comune di Catania

Realizzato da: Mario A. Di Bernardo @Madiber

Supervisore: Dott. Cristiano Longo

Anno Accademico 2014/15

##ABSTRACT
Il progetto presentato mira alla realizzazione di una mappa dei beni confiscati alla criminalità organizzata in gestione al comune di Catania, prendendo spunto da un'analoga implementazione realizzata per il comune di Palermo.
Mediante uno script in PHP, i dati presenti sul sito del comune verranno tradotti in formato RDF/XML e caricati su un server per poter essere così consultabili da un secondo script che, utilizzando la sparql suite, permetterà di costruire e visualizzare la mappa. 
###Criticità iniziali riscontrate
La mappa realizzata per il comune di Palermo è stata ricavata dal dataset del patrimonio rilasciato dal comune stesso, disponibile in formato CSV e distribuito con licenza Creative Commons Attribuzione 4.0 Internazionale al seguente [URI permanente](http://www.comune.palermo.it/opendata_dld.php?id=319).
La tabella fornita dal comune di Catania è invece in formato ODS, è incompleto, mal compilato, presenta diverse anomalie nella formattazione, le informazioni sono talvolta accorpate in un'unica colonna senza seguire alcuno schema ricorrente (es. in una riga indirizzo-civico-tipologia, nella successiva solo indirizzo). Il documento è dunque impossibile da leggere via script. Pertanto, è stato necessario rimaneggiare il file del comune creando un nuovo file CSV.
###Open Data: cosa sono
Secondo la Open Knowledge Foundation:
«un contenuto o un dato si definisce aperto se chiunque è in grado di utilizzarlo, ri-utilizzarlo e ridistribuirlo, soggetto, al massimo, alla richiesta di attribuzione e condivisione allo stesso modo».
Il principio su cui si basa è quello, di più ampio respiro, dell'**open government**, secondo il quale la pubblica amministrazione dovrebbe essere aperta ai cittadini, sia in termini di trasparenza sia di partecipazione diretta al processo decisionale, prediligendo le nuove tecnologie dell'informazione e della comunicazione come canale di diffusione dei dati stessi.

![logo](https://github.com/Madiber/beni_confiscati_catania/blob/master/img/open_data.png)

##SCRIPT DI TRADUZIONE
Il nuovo file CSV è stato quindi convertito in un'ontologia RDF/XML usando uno script PHP che, funzionando da riga di comando, prende sullo standard input il file, lo legge riga per riga e restituisce un documento RDF/XML. Qui, ad ogni edificio sono stati associati, tra gli altri, i dati riguardanti:

 - indirizzo e numero civico;
 - coordinate geografiche ottenute dalle API di Google Maps a partire dai dati al punto precedente;
 - categoria catastale in riferimento a un vocabolario OWL da me realizzato;
 - la disponibilità, ovvero se l'edificio è stato assegnato o meno;
 - l'eventuale associazione assegnataria.

![screen](https://github.com/Madiber/beni_confiscati_catania/blob/master/img/screen.png)

Lo script è stato successivamente modificato per permette la creazione di due ulteriori ontologie RDF/XML: una contenente le sole informazioni sui beni e una con i soli dati di geo localizzazione. Ciò faciliterà future implementazioni che utilizzeranno, al posto delle API di Google, alternative Open che permetteranno di riutilizzare i dati e di visualizzare i punti senza dover per forza usare Google Maps.
###Validazione
Per verificare la correttezza dello script e, quindi, del file risultante è stato utilizzato il validatore RDF online messo a disposizione da W3.
##VOCABOLARIO CATEGORIE CATASTALI
Non avendo trovato nulla di pronto relativo alla classificazione dei Real Estate italiani, partendo dalle categorie elencate sulla Gazzetta Ufficiale è stato modellato – con una gerarchia di classi - un primo vocabolario in OWL utilizzando il software Protege.
Tuttavia, mappare le categorie – descritte in modo informale e quasi colloquiale - nel CSV con la corretta categoria catastale si è rivelato problematico: alcune descrizioni non avevano corrispondenza o quelle possibili erano più d'una. Un nuovo vocabolario è stato quindi definito, questa volta prendendo in considerazione le categorie così come esposte nel CSV. In questo modo le abbreviazioni sono state ricondotte ai termini estesi e si sono risolti i casi in cui la stessa categoria era indicata con due termini diversi (es. “Terr.” e “terreno”). È stata anche prevista una classe “sconosciuta” cui fare riferimento in caso di mancata corrispondenza.
![protege](https://github.com/Madiber/beni_confiscati_catania/blob/master/img/protege.png)

##MAPPA
L'ontologia così creata è stata quindi caricata su Dydra - server che offre endpoint SPARQL - e tramite uno script JAVASCRIPT è stata realizzata una visualizzazione della mappa sfruttando una libreria già esistente.
![logo](https://github.com/Madiber/beni_confiscati_catania/blob/master/img/mappa.png)

##WEBGRAFIA
- Open Knowledge Foundation

https://okfn.org/
- Mappa dei beni confiscati alla criminalità organizzata in gestione al comune di Palermo

http://www.dmi.unict.it/~longo/locn/BeniConfiscatiPalermo.html
- Dataset del patrimonio rilasciato dal comune di Palermo

http://www.comune.palermo.it/opendata_dld.php?id=319
- Sparql suite

http://www.dmi.unict.it/~longo/sparql_suite/
- Elenco dei beni confiscati del comune di Catania

http://www.comune.catania.it/amministrazione-trasparente/beni-immobili-e-gestione-del-patrimonio/patrimonio-immobiliare.aspx
- Validatore RDF del W3

http://www.w3.org/RDF/Validator/
- Categorie catastali secondo la Gazzetta Ufficiale

http://www.gazzettaufficiale.it/catasto/help/categoria
- Protege OWL

http://protege.stanford.edu/products.php#desktop-protege
