# Progetto Vica-Arredo Bagno


## INTERRUZIONE
--------------------------------------------------------------------------------------------------------------------------------------------
Rotte generalmente pronte, va solo gestita bene la rotta degli articoli che al momento non conta i dati relativi a "Dimensioni articolo"
Suggerirei al momento di cominciare a buttare giù il FE anche per far vedere qualcosa al cliente.
--------------------------------------------------------------------------------------------------------------------------------------------


## Introduzione
Questa repository contiene il backend sviluppato interamente in PHP puro per il progetto commissionato da Vica-Arredo Bagno.
L’obiettivo del backend è quello di collegare il frontend al database MySQL, fornendo una serie di endpoint API REST per la gestione e la consultazione dei dati.

Lo stack tecnologico principale comprende:
- PHP (puro) per la logica applicativa
- MySQL / SQL per la gestione del database


## Dipendenze e requisiti
Per eseguire il progetto, assicurati di avere installato:
- PHP >= 8.0
- MySQL >= 8.0
- Apache o un qualsiasi server compatibile con PHP
- Postman per testare gli endpoint (Facoltativo)

## Struttura del progetto
/vica-backend
│
├── config/
│   ├── db.php       # Connessione al database
│   ├── env.php      # Funzione di caricamento file .env
|   └── test-db.php  # Test di verifica collegamente db
├── api/
│   ├── prodotti.php    # Endpoint prodotti
│   ├── catalogo.php    # Endpoint cataloghi
|   ├── recensioni.php  # Endpoint recensioni
│
├── lib/
│   └── 
│
├── query_sql/
|   ├── init_db.sql       # Script di creazione db
|   └── test_seeder.sql   # Inserimento di dati test
|
├── index.php             # Entry point principale
└── README.md             # Documentazione


## Avvio del progetto
Clona la repository:

1. git clone https://github.com/tuo-username/vica-arredo-bagno-backend.git
2. Importa il file .sql nella tua istanza MySQL.
3. Aggiorna le credenziali nel file .env
4. Avvia un server PHP -> php -S localhost:8000
5. Testa gli endpoint tramite Postman o browser.


## Endpoint principali
Metodo	Endpoint	Descrizione
GET	 /api/prodotti/get_all.php	Restituisce la lista di tutti i prodotti
GET	 /api/categorie/get_all.php	Restituisce le categorie disponibili
POST /api/prodotti/create.php	Aggiunge un nuovo prodotto


## Struttura del database
Tabella	Descrizione
prodotti	Contiene i dettagli dei prodotti
categorie	Elenco delle categorie
immagini	Percorsi e riferimenti delle immagini


## Sicurezza
Il backend gestisce la validazione dei dati in input e restituisce risposte JSON standardizzate.
È predisposto per essere integrato con un sistema di autenticazione in caso di estensioni future (es. pannello admin).


## Autore
Sviluppato da Giuseppe Casaburi
Email: [giuseppe.casaburi96@gmail.com]


## Licenza
Questo progetto è rilasciato sotto licenza MIT.