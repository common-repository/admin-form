<div class="af-content-table dbp-docs-content  js-id-dbp-content">
<h2 class="dbp-h2"> <a href="<?php echo admin_url("admin.php?page=admin_form_docs") ?>">Doc</a><span class="dashicons dashicons-arrow-right-alt2"></span><?php _e('Template engine','admin_form'); ?></h2>

<h1 style="border-bottom:1px solid #CCC">Introduzione</h1>
<div class="dbp-help-p">
    <p>In alcune parti del plugin come nel template o nei campi calcolati è possibile inserire del codice personalizzato attraverso un template engine.<br> Questo si divide in due concetti principali le variabili e le funzioni.</p> 

    <p>Puoi altrimenti eseguire le istruzioni del template engine attraverso lo shortcode <b>adfo_tmpl</b>. Aggiungi dall'editor un blocco shortcode e scrivi:
    <pre class="dbp-code">[adfo_tmpl]2*2 = [^MATH 2 * 2] [/adfo_tmpl]</pre></p>

    <p>Nelle liste, ad esempio, i dati estratti sono salvati nella variabile [%data]. Tutte le variabili vengono richiamate con shortcode che iniziano per %. Le funzioni invece ocn shortcode che iniziano con ^ e si comportano sempre nello stesso modo in ogni parte del codice.</p>
    <pre class="dbp-code">[%data.post_title]</pre>
    Stamperà il titolo di un post in una lista in cui vengono estratti i post.<br>
    Per richiamare le proprietà di un oggetto tramite gli shortcode si usa il punto. 
</div>

    <h1 style="border-bottom:1px solid #CCC">STATEMENTS AND FLOW CONTROL</h1>
    <div class="dbp-help-p">
        <h3>MATH</h3>
        <div class="dbp-help-p">
            <pre class="dbp-code">    [^MATH 3 + 1 + .5] [// 4.5 //]
    [^MATH 3+2 * 2] [// 7 //]
    [^MATH (3+2) * 2] [// 10 //]
    [^MATH 2^3] [// 8 (2*2*2) //]
    [^MATH 9^.5] [// 3 (radice quadra) //]</pre>

            Si possono usare gli operatori di relazione > >= < <= != o <> in, not in, ! 
            <pre class="dbp-code">    [^MATH 2 > 1 ] [// 1 //]
    [^MATH 2 <> 1 ] [// 1 //]
    [^MATH 2 in ["1","3","4"] ] [// 0 //]
    [^MATH 2 not in ["1","3","4"] ] [// 1 //]</pre>
            In math è possibile usare anche gli operatori logici: AND && OR || Tornerà 1 se vero 0 se falso
            <pre class="dbp-code"> [^MATH 4 > 5 OR (3 == 3 AND 2 == 2) ] [// 1 //]</pre>
        </div>
     
    <h3>[^IF] ... [^ELSE] ... [^ENDIF]</h3>
    <p>Conditional statement. If the condition is met, the code between [^IF] and [^ELSE] is executed, otherwise the code between [^ELSE] and [^ENDIF] is executed. </p>
    <div class="dbp-help-p">
            <pre class="dbp-code">[^SET a=3]
[^IF [%a]*2 == 6] YES [^ENDIF]</pre>
    </div>
    
    <h3>FOR...ENDFOR</h3>
    <div class="dbp-help-p">
        <p>Cicla i dati.</p>
        <h4 class="dbp-h4">Attributi:</h4>
        <ul>
            <li><b>EACH=</b> imposta l'array da ciclare. Setta anche il nome della variabile a cui passare i valori in automatico a partire dalla prima parola incontrata.
            </li>
            <li><b>VAL=</b> Il nome della variabile a cui passare i dati.</li>
            <li><b>KEY=</b> Se è un array associativo il nome della chiave dell'array.</li>
        </ul>
<pre class="dbp-code">&lt;ul&gt;
    [^FOR EACH=[%data] KEY=mykey VAL=myItem]
        &lt;li&gt;[%mykey] = [%myItem]&lt;/li&gt;
    [^ENDFOR]
&lt;/ul&gt;
[// Oppure se non si usano KEY e VAL //]

&lt;ul&gt;
    [^FOR EACH=[%data]]
        &lt;li&gt;[%key] = [%item]&lt;/li&gt;
    [^ENDFOR]
&lt;/ul&gt;
</pre>

    <p>Genera un elenco di titoli linkabili dei post. Per interrompere un ciclo si può usare l'istruzione break.</p>
        <pre class="dbp-code">&lt;ul&gt;
    [^FOR EACH=[^POST TYPE=post]]
        &lt;li&gt;[%item.title_link]&lt;/li&gt;
    [^ENDFOR]
&lt;/ul&gt;</pre>
       
        <pre class="dbp-code">&lt;ul&gt;
    [^FOR EACH=[&quot;foo&quot;,&quot;bar&quot;,&quot;pippo&quot;] VAL=var]
    [^break [%var] == &quot;bar&quot;]
    &lt;li&gt;[%var]&lt;/li&gt;
    [^ENDFOR]
&lt;/ul&gt;</pre>
        <b>The above example will output:</b>: <br>
        <div class="dbp-result">&bull; foo</div>
    </div>            
    <h2>WHILE... ENDWHILE</h2>
    <div class="dbp-help-p">
        <p>Esegue un ciclo fin tanto che la condizione viene soddisfatta. Per maggiori informazioni sulle condizioni puoi vedere MATH</p>
        <pre class="dbp-code">[^WHILE [%var set+=1] < 10]
    [^SET ris.[]=[%var]]
    [^BREAK [%var]>=5]
[^ENDWHILE]
[^RETURN [%ris SEP=;]]</pre>
        <b>The above example will output:</b>: <br>
        <div class="dbp-result">1 , 2 , 3 , 4 , 5</div>
    </div>
    <h2>[^BREAK]</h2>
    <div class="dbp-help-p">
        <p>Interrompe l'esecuzione di un ciclo FOR o WHILE o del blocco che si sta eseguendo.</p>
        <p>Break viene eseguito se è soddisfatta una condizione. Se non vengono inserite condizioni allora blocca sempre L'esecuzione</p>
        <pre class="dbp-code">[^IF 3==3]
    Lorem ipsum dolor sit amet,
    [^BREAK]
    consectetur adipiscing
[^ENDIF]
Donec et accumsan nulla, at tempus metus
        </pre>
        <p>Non stama "consectetur adipiscing" perché successivo al break, ma stamap "Donec ..." perché fuori dal blocco if. Break infatti interrompe l'esecuzione di un blocco o di un ciclo.</p>
        <b>The above example will output:</b>: <br>
        <div class="dbp-result">Lorem ipsum dolor sit amet, Donec et accumsan nulla, at tempus metus</div>
    </div>   
        
    <h3>COMMENTI</h3>
    <div class="dbp-help-p">
        <p>I commenti non vengono stampati nella pagina</p>
        <pre>[// commento //]</pre>
    </div>
    <h3>[^BLOCK... ENDBLOCK</h3>
    <div class="dbp-help-p">
        <p>Cattura il codice all'interno del tag e lo imposta in una variabile senza eseguirlo. Il codice verrà poi eseguito quando la variabile verrà richiamata. È la cosa più vicina ad una funzione che c'è.</p>
    </div>
</div>

<h1 style="border-bottom:1px solid #CCC">FUNCTIONS</h1>
<div class="dbp-help-p">
    <h3>[^USER]</h3>
    <div class="dbp-help-p">
        <p>Carica un utente wordpress e i suoi metadata. Se non si passa nessun dato ritorna l'utente corrente<br>
        I parametri che possono essere passati sono:<br>
            id|slug|email|login per il caricamento di un singolo utente.<br>Se non si passa nessun parametro allora viene caricato
            l'utente che si è loggato
        </p>
        <h4>La funzione ritorna</h4>
        <ul class="pina-return-properties">
            <li>id: int</li>
            <li>login: string</li>
            <li>email: string</li>
            <li>reoles: array</li>
            <li>registered: string</li>
            <li>nickname: string</li>
            <li>wpx_capabilities: array</li>
            <li>wpx_user_level: int</li>
            <li>meta_*: Altri metadata</li>
        </ul>

        <pre class="dbp-code">[^user]</pre>
        <div class="dbp-result">{"login":"admin","email":"admin^admin.it","roles":["administrator"],"registered":"2020-01-08
            13:31:57","nickname":"admin"}</div>

        <pre class="dbp-code">[^IF [^user]==""]
        non sei loggato
        [^else]
        Sei loggato
        [^endif]</pre>
        Esegue un codice diverso se si è loggati oppure no.
    </div>


    <h3>[^NOW]</h3>
    <div class="dbp-help-p">
        <p>Torna la data di oggi</p>
        <pre class="dbp-code">[^now]
        [^now date-format="d-m-Y" date-modify="+2 days"]
        [^now timestamp]
        </pre>
    </div>
    <h3>[^POST]</h3>
    <div class="dbp-help-p">
        <p>Carica i post di wordpress</p>
        <h4>Gli attributi sono</h4>
        <p>Per estrarre un articolo singolo</p>
        <p><b>id</b>= trova il post con un determinato id. Se è un array di id trova tutti i post con quegli array<br></p>
        <p>Per estrarre più articoli</p>
        <p>
            <b>type</b>= Il post_type (post, page ecc...) Di default è post. Se si vogliono caricare le immagini vedi l'alias di [^POST] <br>
            <b>cat</b>= trova i post di una determinata categoria o di un gruppo di categorie. Accetta l'id, lo slug oppure un array di id<br>
            <b>!cat</b>= trova i post che non sono presenti in una determinata categoria o di un gruppo di categorie. Accetta l'id lo slug oppure un array di id<br>
            <b>author</b>= trova i post per un determinato autore. Se è un numero usa l'id altrimenti lo user_nicename (NON IL NOME)<br>
            <b>slug</b>= Cerca per lo slug.<br>
            <b>tag</b>= Certa un post che abbia almeno uno dei tag selezionati. è possibile scriverli in un oggetto oppure in una stringa.<br>
            <b>parent_id</b>= L'id del post padre.<br>
            <b>limit</b>= Limita il numero di articoli da visualizzare. Per default 10. metti -1 per averli tutti. <br>
            <b>offset</b>= Visualizza gli articoli a partire da<br>
            <b>order</b>= Il campo su cui ordinare<br>
            <b>ASC</b> Ordine crescente<br>
            <b>DESC</b> Ordine decrescente<br>
            Mostrare gli articoli associati in un certo periodo di tempo.<br>
            <b>year</b>= Gli articoli di un determinato anno (es 2020)<br>
            <b>month</b>= Gli articoli di un determinato mese (1-12)<br>
            <b>week</b>= Gli articoli di una determinata settimana (week)<br>
            <b>day</b>= Gli articoli di un determinato giorno (day)<br>
            <b>first</b>= Mostra i primi articoli inseriti. Per default 5. Sostituisce order, asc, desc, limit<br>
            <b>last</b>= Mostra gli ultimi articoli inseriti. Per default 5. Sostituisce order, asc, desc, limit<br>
        </p>
        <p>Ricerca nei postMeta:</p>
        <p>è possibile cercare nei postmeta inserendo il tipo di filtro nell'attributo meta_query. Se si vogliono ricercare più parametri questi possono essere aggiunti divisi da spazi. In automatico verranno collegati come AND. Se si vogliono aggiungere OR e AND all'interno della ricerca questi vengono inseriti come funzioni. Le condizioni all'interno della funzione vengono collegate dalla congiunzione logica inserita. </p>
        <pre class="dbp-code">meta_query=[: AND(a>=b
        OR (
        b<=var c!=ccc L IN (3,2,5,3,52,34) ) .c LIKE ("% ") 
                        .d=" [%pippo]" parametro=) c> 2 :]
        </pre>
        <p>Altri parametri:</p>
        <p><b>read_more</b>= Il testo da mettere nella variabile link_read_more. Se non presente aggiunge ... . Se light_load è presente il tag è inutilizzabile</p>
        <p><b>image</b>= La dimensione dell'immagine di apertura: thumbnail, medium, large, full. Se non impostata carica post-thumbnail</p>
        <p><b>light_load</b> Esclude dal caricamento i post_meta e altri dati aggiunti per semplificare la gestione dei post. Passare  0 o 1 è opzionale</p>
        <h4>La funzione ritorna</h4>
        <ul class="pina-return-properties">
        <li>id: int</li>
        <li>author: text</li>
        <li>*author_id: int</li>
        <li>*author_name: text</li>
        <li>*author_roles: array</li>
        <li>*author_email: text</li>
        <li>*author_link</li>
        <li>date: date</li>
        <li>content: text</li>
        <li>title: text</li>
        <li>*title_link: link</li>
        <li>*permalink: link</li>
        <li>guid: link</li>
        <li>excerpt: text</li>
        <li>status: text</li>
        <li>comment_status: text</li>
        <li>name: text</li>
        <li>modified: date</li>
        <li>parent: int</li>

        <li>menu_order: int</li>
        <li>type: text</li>
        <li>mime_type: text</li>
        <li>comment_count: int</li>
        <li>filter: text</li>

        <li>*read_more_link: link</li>
        <li>*image: html</li>
        <li>*image_link: text</li>
        <li>*image_id: int</li>
        <li>*[postmeta]</li>
        </ul>
        <p>Se presente l'attributo light_load questi dati non vengono caricati. Se il post type è attachment e mime_type è image, permalink, image e image_link vengono comunque caricati. 
        <p>A questi si aggiungono tutti i post meta.</p>   
            
        <h4>Esempi</h4>
        <pre class="dbp-code">[^POSt id=XX for=[:
        [%item for=[:<p><b>[%key]</b>: [%item trim_words] </p>:]]
        :]]</pre>

        <pre class="dbp-code">[^POST get={"id":"id","Titolo":"title_link", "Autore"=>"author_name"} type=post tmpl=table]</pre>
    </div>

    <h3>[^IMAGE]</h3>
    <div class="dbp-help-p">
        <p>Carica le immagini </p>
        <b> Gli attributi sono gli stessi di [^ POST]. In particolare puoi utilizzare: </b>
          <p> <b> id </b> = trova l'immagine con un certo id. Se è un array di ID, trova tutte le immagini dagli id dell'array <br> </p>
          <p>: <b> image_size </b> = La dimensione dell'immagine di apertura: thumbnail, medium, large, full. Se non è impostato, carica la miniatura post. <b>fit</b> ingrandisce l'immagine alla dimensione massima del suo contenitore (larghezza = 100%). <b>winfit</b>:  ridimensiona l'immagine in proporzione allo schermo. Vedere il tutorial 2 per ulteriori informazioni. </p>
        <p>Viene aggiunto:</p>
        <p><b>post_id</b>= Trova tutte le immagini collegate ad un singolo post<br></p>
         <p><b>light_load</b>= è impostato su 0 (nei post è impostato su 1). Se si vuole caricare tutto si deve inserire light_load=0<br></p>
        <h3>La funzione ritorna</h3>
    <p>Ritorna gli stessi parametri di POST</p>
    <ul class="pina-return-properties">
        <li>image: html</li>
        <li>title: text</li>
        <li>*title_link: link</li>
        <li>*permalink: link</li>
        <li>url: string</li>
        <li>original_url: string</li>
        <li>image_link: text</li>
        <li>image_id: int</li>
        <li>*attachment_width: int</li>
        <li>*attachment_height: int</li>
        <li>*attachment_file: int</li>
        <li>*attachment_sizes: Array</li>
        <li>*attachment_image_meta: Array</li>
        <li>id: int</li>
        <li>author: text</li>
        <li>*author_id: int</li>
        <li>*author_name: text</li>
        <li>*author_roles: array</li>
        <li>*author_email: text</li>
        <li>*author_link</li>
        <li>date: date</li>
        <li>content: text</li>
        <li>guid: link</li>
        <li>excerpt: text</li>
        <li>status: text</li>
        <li>comment_status: text</li>
        <li>name: text</li>
        <li>modified: date</li>
        <li>parent: int</li>
        <li>menu_order: int</li>
        <li>type: text</li>
        <li>mime_type: text</li>
        <li>comment_count: int</li>
        <li>filter: text</li>
        <li>*read_more_link: link</li>
        <li>*[postmeta]</li>
    </ul>
    <p>* Se viene inserito light_load=0 questi dati non vengono caricati. Altrimenti non verranno caricati. 
    <p>A questi si aggiungono tutti i post meta.</p>   
      
  <h3>Esempi</h3>
  <pre class="dbp-code">[^IMAGE class=my_gallery print=[%item.image] attr={"id":"myGallery"} ]
    :]]</pre>
    </div>

    <h3>[^CURRENT_POST]</h3>
    <p>Ritorna il post che si sta visualizzando</p>

    <h3>[^LINK]</h3>
    <p>L'equivalente di get_permalink().</p>
    <div class="dbp-help-p">
        <p>Link come dice il nome stesso serve a generare un link del sito.<br>
        Per la scelta della pagina o del post il parametro è page_id. SE page_id non è inserito, il link è alla pagina corrente. Qualsiasi altro parametro inserito viene registrato come nuovo elemento dell'url</p>
        <pre class="dbp-code">&lt;a href=&quot;[^LINK page_id=xxx id=yyy action=zzz]&quot;&gt;link&lt;/a&gt;</pre>
        <h3> Examples </h3>
        <pre class="dbp-code"><a href="[^LINK post_id=[%data.ID]]">[%data.post_title]</a></pre>
        <p>
            Se colleghi a post con post_type personalizzati ricordati di registrare il post_type su  function.php del tuo template</p>
            <pre class="dbp-code">add_action( 'init', 'register_my_post_type' );
function register_my_post_type() {
    register_post_type(
    'my_post_type',
    array (
        'public'             => true,
        'rewrite'            => array('slug' => 'my_post_type'),
        'show_in_menu'      => false,
        'show_in_nav_menus' => false,
        'show_in_rest' 		=> false
    )
    );
}</pre>
    
    </div>

    <h3>[^ADMIN_URL]</h3>
    <div class="dbp-help-p">
        <p>Fa un link ad una lista amministrativa. Puoi passare ulteriori parametri per filtrare i dati passati</p>
            <h4 class="dbp-h4">Parameters</h4>
            <ul>
                    <li><b>id</b><br>
                    (integer) L'id della lista</li>
                    <li><b>some_other_params</b><br>
                    (string) Puoi aggiungere altri parametri che verranno passati nell'url</li>
            </ul>
            <p>Esempio: In una lista mostra i dati di un'altra lista filtrati e il bottone per andare a modificare i dati.<br>
                1. crea una lista collegata ai post con una colonna post_id.<br>
                2. Aggiungi un po' di record collegati ai post.<br>
                3. Crea una seconda lista in cui estrai i post di wordpress<br>
                4. Sulla nuova lista apri List view formatting e clicca in fondo add row<br>
            </p>
            <pre class="dbp-code">[^GET_LIST ID=xx post_id=[%data.ID]]
            &lt;a href=&quot;[^ADMIN_URL id=xx post_id=[%data.ID]]&quot;&gt;Edit&lt;/a&gt;</pre>
    </div>
    
    <h3>[^SET]</h3>
    <div class="dbp-help-p">
        <p>Definisce nuove variabili</p>
        <pre class="dbp-code">[^SET variable=value var2=val2]</pre>
        <p>Per impostare il contenuto di più variabile è possibile scrivere:</p>
        <pre class="dbp-code">[^SET myvar="FOO" mynewvar="bar"]
    [%myvar set="FOO"]</pre> 
        <p>La differenza tra le due scritture è che nel primo caso la funzione [^SET ...] imposta una o più variabili senza stamparle, mentre nel secondo esempio la variabile viene impostata e stampata</p>
        <p>Gli shortcode possono lavorare anche con contenuti non impostati in variabili:</p>
    <pre class="dbp-code">[%"Ecco il mio testo"] [//  stampa il testo //]
    [%[1,2,3,4,5,6,7,8,9] ] [// stampa il json dell'array //]</pre>
        <pre class="dbp-code">[^SET 
   a="Foo | part1 | part2.3" 
   b=[%a split=|] 
   c=[%b.2 split=.]
]
[^RETURN [%c.1]] [// output 3 //]
</pre>

    <p>è possibile inserire istruzioni sia nei valori che nei nomi delle variabili purché si rispetti la regola di non mettere spazi</p>
    <pre class="dbp-code">    [^SET ser='a:3:{s:1:"a";i:1;s:1:"b";i:2;s:1:"c";i:3;}' unser={"d":4,"e":5,"f":6}]
    [^SET nu=[%ser unserialize]]
    [%nu.c] | [%unser.d]</pre>
    <p>Nell'esempio vengono impostati un array (unser) e una stringa serializzaa (ser). Poi viene settata una nuova variabile nu con l'array estratto da ser, infine viene ciclato unser e i suoi valori vengono inseriti in nu.</p>
  </div>

  <h3>[^COUNTER]</h3>
  <div class="dbp-help-p">
  <p>Ogni volta che viene chiamata ritorna un numero nuovo di una sequenza numerica</p>
  <h4>Gli attributi optzionali sono</h4>
  <p><b>name</b>= Il nome del counter che vuole essere richiamato</p>
  <p><b>start</b>= Il numero da cui vuoi far iniziare il counter</p>
  <p><b>step</b>= il numero di unità di per cui aumentre o diminuire il counter ogni volta che viene chiamato</p>
  </div>

  <h3>[^GET_THE_ID]</h3>
  <div class="dbp-help-p">
    <p>Torna l'id di un post, identico a get_the_id di wordpress</p>
  </div>
  <h3>[^RETURN]</h3>
    <div class="dbp-help-p">
        <p>Cancella tutta la parte di testo fino a quel punto e stampa unicamente il valore del return. Questa funzione è molto utile se state scrivendo un codice che è composto da più righe per evitare che vengano stampta</p>
        <p>Non stamperà "Il mio risultato", ma solo il valore di my_var.</p>
        <pre class="dbp-code">Il mio risultato: [^RETURN [%my_var]]</pre>
    </div>


    <h3>[^IS_USER_LOGGED_IN]</h3>
    <div class="dbp-help-p">
        <p>Torna 1 se l'utente è loggato altrimenti 0</p>
    </div>

    <h3>[^IS_ADMIN]</h3>
    <div class="dbp-help-p">
        <p>Torna 1 se è una pagina amministrativa</p>
    </div>

     <h3>is_*</h3>
    <div class="dbp-help-p">
        <p>[^IS_PAGE_AUTHOR], [^IS_PAGE_ARCHIVE], [^IS_PAGE_TAG], [^IS_PAGE_DATE], [^IS_PAGE_TAX] [^IS_PAGE], [^IS_SINGLE]</p>
        <p>Torna 1 se è la pagina richiesta, altrimenti 0 </p>
    </div>


    <h3>[^GET_TAG] or [^GET_CAT]</h3>
    <div class="dbp-help-p">
        <p>Ritorna i dati del tag o di una categoria</p>
        <h4 class="dbp-h4">Attributes</h4>
        <ul>
            <li><b>id or term_id</b></li>
            <li><b>slug</b></li>
            <li><b>name</b></li>
        </ul>
        <h4 class="dbp-h4">Return</h4>
        <ul>
            <li><b>id </b></li>
            <li><b>term_id</b></li>
            <li><b>name</b></li>
            <li><b>slug</b></li>
            <li><b>term_group</b></li>
            <li><b>taxonomy</b></li>
            <li><b>parent</b></li>
            <li><b>link</b></li>
            <li><b>html</b></li>
        </ul>
        <pre class="dbp-code">[^GET_TAG.html id=2]</pre>
    </div>


    <h3>[^GET_POST_TAGS] or [^GET_POST_CATS]</h3>
    <div class="dbp-help-p">
        <p>Ritorna i dati dei tag o delle categorie di un post</p>
        <h4 class="dbp-h4">Attributes</h4>
        <ul>
            <li><b>post_id</b></li>
        </ul>
        <h4 class="dbp-h4">Return</h4>
        <ul>
            <li><b>id </b></li>
            <li><b>term_id</b></li>
            <li><b>name</b></li>
            <li><b>slug</b></li>
            <li><b>term_group</b></li>
            <li><b>taxonomy</b></li>
            <li><b>parent</b></li>
            <li><b>link</b></li>
            <li><b>html</b></li>
        </ul>
        <pre class="dbp-code">[^GET_POST_CATS.html post_id=2]</pre>
    </div>

    <h1 style="border-bottom:1px solid #CCC">Funzioni specifiche per le liste</h1>
    <h3>[^LINK_DETAIL]</h3>
    <p>Paramentri item=[%data], dbp_id=xx, action=""</p>
    <div class="dbp-help-p">
        <p>Crea il link per mostrare il dettaglio di una pagina. Funziona solo mentre si sta ciclando una lista</p>
    </div>
    <h4>Examples</h4>
    <pre class="dbp-code">&lt;a href=&quot;[^LINK_DETAIL]&quot; class=&quot;js-dbp-popup&quot;&gt;popup detail&lt;/a&gt;</pre>

    <h3>[^UNIQ_CHARS_ID]</h3>
    <p>Params item=[%data], dbp_id=xx</p>
    <div class="dbp-help-p">
        <p>Genera una stringa univoca per richiamare il record.<br> Questo risolve il problema che, se la query è composta da più tabelle, per estrarre una riga specifica bisogna passare gli id di tutte le tabelle.</p>
        <h4>Examples</h4>
        <p>1. In list view formatting crea un custom code e aggiungi questo codice:</p>
            <pre class="dbp-code">&lt;a href=&quot;?k=[^uniq_chars_id]&quot;&gt;[^uniq_chars_id]&lt;/a&gt;</pre>
        <p>2. In functions.php aggiungi il seguente codice sostituendo {the_list_id} con l'id della lista che si sta lavorando.</p>
        <pre class="dbp-code">if (isset($_REQUEST['k'])) {
	var_dump(admin_form\ADFO::get_detail({the_list_id}, $_REQUEST['k']));
    die;
}</pre>
    </div>

    <h3>[^GET_LIST id=""]</h3>
    <p>Stampa una lista specifica.</p>

    <h3>[^GET_SINGLE id=""]</h3>
    <p>Stampa i risultati di un singolo record.</p>

    <h3>[^GET_DETAIL dpb_id="" id=""]</h3>
    <p>Estrae i dati di un record specifico.</p>

    <h3>[^GET_TOTAL dpb_id="" ]</h3>
    


    <pre class="dbp-code">[^GET_LIST_DATA id=xxx tmpl=[:
    &lt;h3&gt;[%item.title]&lt;/h3&gt;
    &lt;p&gt;[%item.description]&lt;/p&gt;
:]]</pre>

    <h3>[^BTN_EXPORT  id="" format=csv|sql title="" class=""]</h3>
    <p>Crea il bottone per scaricare una lista.</p>

</div>  

<h1 style="border-bottom:1px solid #CCC">VARIABLES</h1>
    <div class="dbp-help-p">
        <p>Si possono aggiungere variabili o json come valori degli attributi</p>
        <pre class="dbp-code"> [%myvar set=[^POST last]] 
 [%myvar set=["foo","bar"]]
 [%myvar set={"a":"foo","b":"bar"}]</pre>
        <p>Non si possono mettere variabili al posto dei nomi degli attributi</p>
        <pre class="dbp-code">[%myvar [%var]="foo"] [// NON CORRETTO //] </pre>
        <p>Questo è permesso solo dentro la funzione [^SET ] purché non ci siano spazi</p>
        <pre class="dbp-code">[^SET [%var]="foo"] [// CORRETTO //] 
[^SET mypost.[%var]="foo"] [// CORRETTO //] </pre>

        <p>Si possono chiamare i parametri di un oggetto tramite il .*</p>
        <pre class="dbp-code">[%post.title] 
[%post.0.title]</pre>
        <p>Questo ritornerà una stringa se c'è un solo post, altrimenti un array di titoli.</p>
    </div>
    
    <div class="dbp-help-p">
        <h3 class="dbp-h3">Attributes</h3>
        <div class="dbp-help-p">
            <p>Gli attributi modificano una variabile o una funzione. Possono avere un valore associato.</p>
            <p><b>NON SI può mettere lo spazio accanto al simbolo =.</b></p>
            <pre class="dbp-code">
                [%"string" uppercase]
            </pre>
            <div class="code">
                [%"1602288000" date-format="Y-m-d"]
            </div>

            <h2>default=</h2>
            <p>Se il valore o l'oggetto è vuoto restituisce il default</p>
            <pre class="dbp-code">
            [%novar default="foo"]
            </pre>

            <h2>*=[::]</h2>
            <p>I valori degli attributi non devono avere spazi. </p>
            <p>Se si deve mettere uno spazio bisogna inserire il testo tra virgolette oppure i simboli [: :]. Le virgolette all'interno di un testo già virgolettato devono essere aggiunte col backslash</p>
            <pre class="dbp-code">
            [%myvar default=foobar] [// corretto //]
            [%myvar default=foo bar] [// non corretto //]
            </pre>

            <pre class="dbp-code">
            [%myvar default=[:foo bar:]] [// corretto //]
            [%myvar default="foo bar"] [// corretto //]
            [%myvar default='foo bar'] [//  corretto //]
            </pre>

        </div>   
    </div>

<div class="dbp-help-p">
    <h3 class="dbp-h3">Verifica se è</h3>
    <div class="dbp-help-p">
        <h3>is_string</h3>
        <div class="dbp-help-p">
            Verifica se la variabile è una stringa
        </div>
        <h3>is_date</h3>
        <div class="dbp-help-p">
            Verifica se una variabile è una data valida
        </div>
        <h3>is_object</h3>
        <div class="dbp-help-p">
            Verifica se una variabile è un array o un oggetto
        </div>
    </div>
    

    <h3 class="dbp-h3">Attributi dei testi</h3>
    <div class="dbp-help-p">

   
    <div class="dbp-help-p">
        <h3>val</h3>
        <p>Nei risultati estratti da una lista ne ritorna il valore non modificato. Ad esempio in un campo select dove i valori sono sostituiti da label ritorna il valore o in un campo media library ritorna l'id dell'immagine.</p>
        <pre class="dbp-code">[data.column val]</pre>
    </div>

    <h3>upper - <span class="dbp-help-synonyms">(Synonyms: uppercase strtoupper)</span></h3>
    <div class="dbp-help-p">
        <p>Trasforma una stringa tutta maiuscola</p>
        <pre class="dbp-code">[%"foo" upper]</pre>
        <div class="dbp-result">FOO</div>
    </div>
    <h3>lower - <span class="dbp-help-synonyms">(Synonyms: strtolower lowercase)</span></h3>
    <div class="dbp-help-p">
        <p>Trasforma una stringa tutta minuscola</p>
        <pre class="dbp-code">[%"MY FOO" lower]</pre>
        <div class="dbp-result">my foo</div>
    </div>
    
    <h3>ucfirst  - <span class="dbp-help-synonyms">(Synonyms: capitalize)</span></h3>
    <div class="dbp-help-p">
        <p>Trasforma il primo carattere di una stringa in maiuscolo</p>
        <pre class="dbp-code">[%"my foo" ucfirst]</pre>
        <div class="dbp-result">My foo</div>
    </div>

    <h3>strip-comment - <span class="dbp-help-synonyms">(Synonyms: strip_comment stripcomment)</span></h3>
    <div class="dbp-help-p">
        <p>Rimuove i commenti &lt;!-- --&gt; o // o /* */ </p>
        <pre class="dbp-code">[^SET myvar=" &lt;div&gt;testo&lt;/div&gt;  &lt;!-- un commento--&gt; &lt;i&gt;testo&lt;/i&gt;"]
        [%myvar htmlentities]&lt;br&gt;
        [%myvar strip-comment htmlentities]
        </pre>
        <div class="dbp-result"> &lt;div&gt;testo&lt;/div&gt; &lt;!-- un commento--&gt; &lt;i&gt;testo&lt;/i&gt;<br>
            &lt;div&gt;testo&lt;/div&gt; &lt;i&gt;testo&lt;/i&gt;</div>
        <pre class="dbp-code">[^SET myvar="&lt;script&gt; a =\&quot;foo\&quot;; 
        /* other comment 
        * multiline
        */
        alert(a);
        &lt;/script&gt;"]
        [%myvar htmlentities nl2br]&lt;br&gt;&lt;hr&gt;
        [%myvar strip-comment htmlentities nl2br]
        </pre>
        <div class="dbp-result">&lt;script&gt; a = &quot;foo&quot;;<br>
                /* other comment<br>
                * multiline<br>
                */<br>
                alert(a);<br>
            &lt;/script&gt;<br>
            &lt;script&gt; a = &quot;foo&quot;;<br>
        <br>
                alert(a);<br>
            &lt;/script&gt;<br></div>
    </div>

    <h3>strip-tags  - <span class="dbp-help-synonyms">(Synonyms: strip_tags striptags)</span></h3>
    <div class="dbp-help-p">
        <p>Rimuove tutti i tag html dal testo</p>
        <h2>nl2br</h2>
        <p>Trasforma gli accapi in br</p>
        <h2>htmlentities</h2>
        <p>Trasforma i caratteri speciali in entità html</p>
        <pre class="dbp-code">
        &lt;textarea&gt;[%&quot;&lt;/textarea&gt;&lt;b&gt;fff&lt;/b&gt;&quot; htmlentities]&lt;/textarea&gt;
        </pre>
        <p> l'esempio mostra come attraverso l'attributo htmlentities è possibile scrivere all'interno di una textarea dei tag html</p>
    </div>
    <h3>left=</h3>
    <div class="dbp-help-p">
        <p>Accetta un parametro numerico. <br>Prende i primi n caratteri del testo. Accetta un secondo attributo "more" per aggiungere del testo se left ha effettivamente tagliato la stringa</p>
        <pre class="dbp-code">    [%"A1B2C3D4E5F6G7H8I9" left=5 more=" ..."]</pre>
        <div class="dbp-result"> A1B2C ...</div>
        <p>se il testo viene tagliato è possibile aggiungere un testo a fine riga utilizzando l'attributo more</p>
        <pre class="dbp-code">
        [%"Hello George" left=5 more=" ..."]
        [%"good afternoon" left=25 more=" ..."]
        </pre>
        <p>Nel primo caso taglia il testo e quindi mette il testo dell'attributo more, nel secondo caso non taglia il testo per cui non mette il testo del more</p>
        <div class="dbp-result">Hello ... good afternoon</div>
    </div>
    <h3>right=</h3>
    <div class="dbp-help-p">
        <p>Accetta un parametro numerico. <br>Prende i primi n caratteri del testo</p>
        <pre class="dbp-code">[%"Hello George" right=6]</pre>
        <div class="dbp-result">George</div>
    </div>
    <h3>trim_words=</h3>
    <div class="dbp-help-p">
        <p>Accetta un parametro numerico. <br>Prende le prime n parole del testo</p>
        <p>se il testo viene tagliato è possibile aggiungere un testo a fine riga utilizzando l'attributo more</p>
        <pre class="dbp-code">[%"Hello George how are you?" trim_words=2 more=" [^link id=2 text="..."]"]</pre>
        <div class="dbp-result">Hello George <a href="#">...</a></div>
    </div>
    <h3>sanitize</h3>
    <div class="dbp-help-p">
        <p>Esegue la funzione wordpress sanitize_text_field</p>
    </div>
    <h3>esc_url</h3>
    <div class="dbp-help-p">
        <p>Esegue la funzione wordpress esc_url</p>
    </div>
    <h3>trim</h3>
    <div class="dbp-help-p">
        <p>Rimuove gli spazi prima e dopo in un testo o di tutti i campi di un array</p>
    </div>
    <h3>split=</h3>
    <div class="dbp-help-p">
        <p>Divide un testo in un array</p>
        <pre class="dbp-code">[^SET a=[%"Hello | World" split="|"]][%a.1] [// World //]</pre>
    </div>
    <h3>Search=</h3>
    <div class="dbp-help-p">
        <p>Ritorna 1 se trova la sottostringa o 0 se non lo trova. </p>
        <pre class="dbp-code">[%"Nel mezzo del cammin di nostra vita" search="nostra" ]</pre>
        <div class="dbp-result">1</div>
        <p>Se viene invece passato il parametro replace sostituisce la stringa</p>
        <pre class="dbp-code">[%"Nel mezzo del cammin di notra vita" search="notra" replace="&lt;b&gt;nostra&lt;/b&gt;" ]</pre>
        <div class="dbp-result">Nel mezzo del cammin di <b>nostra</b> vita</div>
    </div>
    <h3>if=</h3>
    <div class="dbp-help-p">
        <p>
        Mostra il campo se la condizione viene rispettata. La condizione la si può inserire tra virgolette oppure tra parentesi quadre con due punti [: ... :]
        </p>
        <pre class="dbp-code">
            [^POST type=post if=[: [%item.id]>30 :] length]
        </pre>
        <p>Conta il numero di articoli con id > 30</p>
    </div>
    <h3>set=</h3>
    <div class="dbp-help-p">
        <p>Imposta il valore di una variabile</p>
        <pre class="dbp-code">[%myvar set="foo"]</pre>
        <div class="dbp-result">foo</div>

        <p>set+= o set-= per sommare o sottrarre la variabile passata</p>
    </div>
    <h3>zero= - <span class="dbp-help-synonyms">(Synonyms: empty)</span></h3>
    <div class="dbp-help-p">
        <p>Stampa un testo alternativo se la variabile è 0 o vuota.</p>
    </div>
    <h3>one= - <span class="dbp-help-synonyms">(Synonyms: singular)</span></h3>
    <div class="dbp-help-p">
        <p>Stampa un testo alternativo se la variabile è 1.</p>
    </div>

    <h3>one= - <span class="dbp-help-synonyms">(Synonyms: singular)</span></h3>
    <div class="dbp-help-p">
        <p>Stampa un testo alternativo se la variabile è 1.</p>
        <pre class="dbp-code">[^SET a=["foo"] ]
[%a count] [%a singular="Item" plural="Items"]</pre>
    </div>
    <h3>plural=</h3>
    <div class="dbp-help-p">
        <p>Stampa un testo alternativo se la variabile è maggiore di 1.</p>
        <pre class="dbp-code">[^SET a=["3","23","65"] ]
[%a count] [%a singular="Item" plural="Items"]</pre>
    </div>

    <h3>negative=</h3>
    <div class="dbp-help-p">
        <p>Stampa un testo alternativo se la variabile è negativa</p>
    </div>

    <h3>decode_ids</h3>
    <div class="dbp-help-p">
        <p>Nella pagina del dettaglio ritorna l'array degli id del record.</p>
        <pre class="dbp-code">[^SET ids=[%request.dbp_ids decode_ids]] [%ids]</pre>
    </div>

    <h3>json</h3>
    <div class="dbp-help-p">
        <p>Converte una stringa formattata come un json in un array</p>
        <pre class="dbp-code">[%my_json_var json tmpl[:&lt;p&gt;[%key]=[%item.title]&lt;/p&gt;:]]]</pre>
    </div>

    <h3>unserialize</h3>
    <div class="dbp-help-p">
        <p>Converte una stringa formattata come un serialize php in un array</p>
        <pre class="dbp-code">[%my_serialize_var json tmpl[:&lt;p&gt;[%key]=[%item.title]&lt;/p&gt;:]]]</pre>
    </div>



    </div>


    <h3 class="dbp-h3">Attributi per i numeri</h3>
    <div class="dbp-help-p">
        <h3>set+=  set-=</h3>
        <div class="dbp-help-p">
            Per sommare o sottrarre la variabile passata.
        </div>

        <h3>decimal=</h3>
        <div class="dbp-help-p">
            <p>Imposta il numero di valori dopo la virgola da mostrare. Accetta altri due parametri dec_point e thousands_sep</p>
            <pre class="dbp-code">[%"1203.23" decimal=1]&lt;br&gt;
[%"1203.23" decimal=1 dec_point=, thousands_sep=.]</pre>
            <div class="dbp-result">1203.2
            1.203,2</div>
        </div>
        
        <h3>euro</h3>
        <div class="dbp-help-p">
            Formatta un numero come valuta euro
        </div>
        <h3>floor</h3>
        <div class="dbp-help-p">
        Arrotonda per difetto il valore di un numero
        </div>

        <h3>round</h3>
        <div class="dbp-help-p">
        Arrotonda il valore un numero
        </div>

        <h3>ceil</h3>
        <div class="dbp-help-p">
        Arrotonda per eccesso il valore di un numero
        </div>
    </div>

    <h3 class="dbp-h3">Attributi per le date</h3>
    <div class="dbp-help-p">

        <h3>date-format=</h3>
        <div class="dbp-help-p">
            <p>Accetta un parametro di testo. <br> Cambia il formato della data</p>
            <pre class="dbp-code">
            [%"2020-10-10" date-format='Y']
            </pre>
            <div class="dbp-result">2020</div>
            <p>Accetta sia date, timestamp o stringhe anno mese giorno tutto attaccato o anche con orario</p>
            <pre class="dbp-code">[%"1602288000" date-format="Y-m-d"]</pre>
            <div class="dbp-result">2020-10-10</div>
            <p>Il seguente esempio prende una variabile item.post_date in formato testo day/month/year, lo converte in year-month-day e stampa Y-m-d H:i:S</p>
            <pre class="dbp-code">[^SET date=[:[%item.post_date left=10 right=4]-[%item.post_date left=5 right=2]-[%item.post_date left=2]:]][%date date-format="Y-m-d H:is"]</pre>

        </div>
        <h3>date-modify=</h3>
        <div class="dbp-help-p">
            <p>Accetta un parametro di testo. <br>Modifica una data</p>
            <pre class="dbp-code">[%"2020-10-10" date-modify="+2 days"]</pre>
            <div class="dbp-result"> 2020-10-12</div>
        </div>

        <h3>last-day</h3>
        <div class="dbp-help-p">
            <p>Prende una data e imposta l'ultimo giorno del mese</p>
            <pre class="dbp-code">[%"2020-10-10" last-day]</pre>
            <div class="dbp-result">2020-10-31</div>
        </div>

        <h3>timestamp</h3>
        <div class="dbp-help-p">
            <p>Prende una data e la converte in un timestamp</p>
            <pre class="dbp-code">[%"2020-10-10" timestamp]</pre>
            <div class="dbp-result">1602288000</div>
        </div>


        <h3>datediff-year=</h3>
        <div class="dbp-help-p">
            Ritorna la differenza in anni tra due date
        </div>
        <h3>datediff-month=</h3>
        <div class="dbp-help-p">
            Ritorna la differenza in mesi tra due date
        </div>
        <h3>datediff-day=</h3>
        <div class="dbp-help-p">
            <p>Ritorna la differenza in giorni tra due date </p>
           
            <pre class="dbp-code">Sono passati: [%a set='2001-10-04 10:20:10' datediff-day='2001-09-02 10:30:00']</pre>
        </div>
        <h3>datediff-hour=</h3>
        <div class="dbp-help-p">
            Ritorna la differenza in ore tra due date
        </div>
        <h3>datediff-minute=</h3>
        <div class="dbp-help-p">
            <p>Ritorna la differenza in minuti tra due date </p>
        </div>

        <h3>timenum-to-timestr</h3>
        <div class="dbp-help-p">
            <p>I campi time vengono salvati in secondi nel database. Questo attributo trasforma i secondi nel formato time hh:mm </p>
        </div>

        <h3>timestr-to-timenum</h3>
        <div class="dbp-help-p">
            <p>I campi time vengono salvati in secondi nel database. Questo attributo trasforma i il valore dal formato time hh:mm in secondi. </p>
        </div>

        <h3>time-to-hour</h3>
        <div class="dbp-help-p">
            <p>Questo attributo calcola il numero di ore da un campo time, quindi in secondi, o da un valore time nel formato hh:mm . </p>
        </div>
        
        
    </div>


    <h3 class="dbp-h3">Attributi negli array</h3>
    <div class="dbp-help-p">
        <h3>Tmpl= - <span class="dbp-help-synonyms">(Synonyms: for, print)</span></h3>
        <div class="dbp-help-p">
            <p>Stampa i dati all'interno di un template. Questo può essere in una variabile*, un template in php esterno oppure scritto all'interno del valore dell'attributo. I dati dentro il template vengono ciclati all'interno della variabile [%item] e [%key] per il nome o il numero della variabile passata.</p>
            <p>* Le variabili vengono trasformate, ma il codice al loro interno non viene eseguito per problemi di performace... bisogna mettere [::] allora il codice all'interno se è una singola variabile viene rielaborata!
        <pre class="dbp-code">[^POST post_type=post tmpl=[:&lt;p&gt;[%key]=[%item.title]&lt;/p&gt;:]]
        </pre>

        <pre class="dbp-code">[%[&quot;1&quot;,&quot;2&quot;,&quot;3&quot;,&quot;4&quot;,&quot;5&quot;] for=[: 
    &lt;h2&gt;ID=[%item]&lt;/h2&gt;
    [^POST id=[%item] tmpl=[:
        &lt;p&gt;[%key]=[%item.title]&lt;/p&gt;
    :]]
:]]</pre>
        </div>
        <h3>.(variable_name)=</h3>
        <div class="dbp-help-p">
            È possibile aggiungere valori ad un array semplicemente aggiungendo attributi preceduti dal punto
        </div>
        <h3>sep=</h3>
        <div class="dbp-help-p">
            Accetta un parametro di testo. <br>unisce i valori di un array in un testo separato dal testo indicato. Sinonimo di implode in php
        </div>
        <h3>qsep=</h3>
        <div class="dbp-help-p">
            Uguale a sep ma unisce con le virgolette il testo
        </div>

        <h3>if=</h3>
        <div class="dbp-help-p">
            <p>
            Mostra il campo se la condizione viene rispettata. La condizione la si può inserire tra virgolette oppure tra parentesi quadre con due punti [: ... :]
            </p>
            <pre class="dbp-code">[^POST type=post if=[: [%item.id]>30 :] length]</pre>
            <p>Conta il numero di articoli con id > 30</p>

            <pre class="dbp-code">[^POST type=post if=[:[%item.author_name] == 'admin':] ]</pre>
            <p>Estrae solo gli articoli con autore = 2</p>
        </div>

        <h3>sum</h3>
        <div class="dbp-help-p">
            Fa la somma di un vettore.
            <p>TODO può essere passato un parametro aggiuntivo per cui fa la somma di un campo di un oggetto (ad esempio age, fa le somme dell'età degli utenti)</p>
        </div>

        <h3>mean</h3>
        <div class="dbp-help-p">
            Fa la media matematica
        </div>

        <h3>count - <span class="dbp-help-synonyms">(Synonyms: length)</span></h3>
        <div class="dbp-help-p">
            <p>Se è un array conta il numero di righe. Se è una stringa conta il numero di caratteri</p>
            <pre class="dbp-code">[%["bar","foo"] length]&lt;br&gt;
    [%"foo" length]</pre>
            <div class="dbp-result">2 // è un array di due elementi
    3 // il numero di caratteri della stringa </div>
        </div>

        <h3>get=  - <span class="dbp-help-synonyms">(Synonyms: show, fields)</span></h3>
        <div class="dbp-help-p">
            <p>Restituisce solo alcuni determinati campi di un array. Se l'array è associativo sostituisce il nome del campo con la nuova chiave</p>
            <pre class="dbp-code">[^post id=3 fields={"titolo":"title"}]</pre>
            <div class="dbp-result">array (size=1)
    'titolo' => string '...' (length=14)</div>
            <pre class="dbp-code">[^post type=page fields=["id","author","title"]]</pre>
            <div class="dbp-result">    0 =>
        array (size=3)
        'ID' => int 1
            'author' => string '1' (length=1)
            'title' => string '...' (length=9)
            1 =>
            array (size=3)
            'ID' => int 2
            'author' => string '1' (length=1)
            'title' => string '...' (length=15)</div>
    
            <pre class="dbp-code">[^post type=page fields=id]</pre>
            <div class="dbp-result"> array (size=2)
                0 => int 1
                1 => int 2 </div>
            <pre class="dbp-code">[^post type=page fields={"id":"id", "autore":"author"} tmpl=table]</pre>
            <p>Stampa una tabella i cui titoli sono id e autore.</p>
        </div>
    
    </div>
    </div>
</div>