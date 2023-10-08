<?php

return [

    /*
     *
     * Shared translations.
     *
     */
    'title' => 'KeizerPHP Installer',
    'next' => 'Volgende stap',

    /*
     *
     * Home page translations.
     *
     */
    'welcome' => [
        'title'   => 'Welkom',
        'message' => 'Dankjewel voor het kiezen voor KeizerPHP. Wanneer je dit ziet moet je nog beginnen aan het installatieproces. Hiervoor gaan we eerst wat vereisten controleren, daarna vragen we om een aantal technische gegevens (zoals de gegevens van de database). Tenslotte zul je een Admin-account aanmaken en de beginwaardes voor de competitie configureren.',
        'next' => 'Controleer vereisten',
    ],

    /*
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'title' => 'Stap 1 /5 | Vereisten',
        'next' => 'Controleer Permissies',
    ],

    /*
     *
     * Permissions page translations.
     *
     */
    'permissions' => [
        'title' => 'Stap 2 / 5 | Permissies',
        'next' => 'Configureer de technische omgeving',
    ],

    /*
     *
     * Environment page translations.
     *
     */
    'environment' => [
        'wizard' => [
            'title' => 'Stap 3 / 5 | Omgevingsinstellingen',
            'form' => [
                'name_required' => 'Een omgevingsnaam is verplicht!',
                'app_name_label' => 'Applicatienaam',
                'app_name_placeholder' => 'Applicatienaam',
                'app_environment_label' => 'Applicatieomgeving',
                'app_environment_label_local' => 'Lokaal',
                'app_environment_label_developement' => 'Ontwikkel',
                'app_environment_label_qa' => 'Test',
                'app_environment_label_production' => 'Productie',
                'app_environment_label_other' => 'Anders',
                'app_environment_placeholder_other' => 'Beschrijf',
                'app_debug_label' => 'Applicatie Debug Modus',
                'app_debug_label_true' => 'Waar',
                'app_debug_label_false' => 'Niet Waar',
                'app_log_level_label' => 'Applicatie Log Level',
                'app_log_level_label_debug' => 'debug',
                'app_log_level_label_info' => 'informatie',
                'app_log_level_label_notice' => 'Opmerkingen',
                'app_log_level_label_warning' => 'Waarschuwingen',
                'app_log_level_label_error' => 'Fouten',
                'app_log_level_label_critical' => 'Kritiek',
                'app_log_level_label_alert' => 'Opletten',
                'app_log_level_label_emergency' => 'Nood',
                'app_url_label' => 'Applicatie Url',
                'app_url_placeholder' => 'Applicatie Url (link naar website)',
                'db_connection_failed' => 'Verbinding maken met Database is niet gelukt!',
                'db_connection_label' => 'Database Connectie',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'Database Host',
                'db_host_placeholder' => 'Database Host',
                'db_port_label' => 'Database Port',
                'db_port_placeholder' => 'Database Port',
                'db_name_label' => 'Database Naam',
                'db_name_placeholder' => 'Database Naam',
                'db_username_label' => 'Database Gebruikersnaam',
                'db_username_placeholder' => 'Database Gebruikersnaam',
                'db_password_label' => 'Database Wachtwoord',
                'db_password_placeholder' => 'Database Wachtwoord',

                'app_tabs' => [

                    'mail_label' => 'Mail',
                    'mail_driver_label' => 'Mail Driver',
                    'mail_driver_placeholder' => 'Mail Driver',
                    'mail_host_label' => 'Mail Host',
                    'mail_host_placeholder' => 'Mail Host',
                    'mail_port_label' => 'Mail Port',
                    'mail_port_placeholder' => 'Mail Port',
                    'mail_username_label' => 'Mail Gebruikersnaam',
                    'mail_username_placeholder' => 'Mail Gebruikersnaam',
                    'mail_password_label' => 'Mail Wachtwoord',
                    'mail_password_placeholder' => 'Mail Wachtwoord',
                    'mail_encryption_label' => 'Mail Encryptie',
                    'mail_encryption_placeholder' => 'Mail Encryptie',
                    'mail_from_label'=>'Mail Van Adres',
                    'mail_from_placeholder'=>'Mail Van Adres',

                    'vapid_help_label'=>'Kopieer de tekens tussen de "" voor zowel de publieke als private sleutel van de site hier beneden.',
                    'vapid_get_label'=>'Verkrijg je VAPID Sleutels (nodig om Pushnotificaties te verzenden)',
                    'vapid_public_label'=>'Vapid Publieke Sleutel',
                    'vapid_public_placeholder'=>'Vapid Publieke Sleutel',
                    'vapid_private_label'=>'Vapid Private Sleute',
                    'vapid_private_placeholder'=>'Vapid Private Sleutel',
                ],
                'buttons' => [
                    'setup_database' => 'Setup Database',
                    'setup_application' => 'Setup Application',
                    'install' => 'Installeer',
                ],
            ],
        ],   
        'save' => '.env Opslaan',
        'success' => 'Uw .env bestand is opgeslagen.',
        'errors' => 'Het is niet mogelijk om een .env bestand aan te maken, maak a.u.b het bestand zelf aan.',
    ],

    'admin' => [
        'title' => 'Stap 4 / 5 | Admin Account',
        'name' => 'Naam',
        'id' => 'KNSB Nummer',
        'rating' => 'KNSB Rating',
        'available' => 'Beschikbaar',
        'email' => 'E-mail Adres',
        'password' => 'Wachtwoord',
        'confirm' => 'Herhaal wachtwoord',
        'register' => 'Maak Admin Account',
    ],

    'configs' =>
    [
        'title' => 'Stap 5 / 5 | Competitie Configuration',
        'competition' => 'Competitienaam',
        'season' => 'Seizoen',
        'endseason' => 'Einde van seizoen (standaard: 0)',
        'start' => 'Startwaarde (hoogste)',
        'step' => 'Stapgrootte tussen elke waarde',
        'between' => 'Rondes tussen gelijke indelingen',
        'betweenBye' => 'Rondes tussen weer tegen Bye kunnen komen',
        'Bye' => 'Score voor Bye',
        'Presence' => 'Score voor aanwezigheid',
        'Club' => 'Score voor afwezigheid i.v.m. Club',
        'Personal' => 'Score voor afwezigheid i.v.m. Persoonlijke Omstandigheden',
        'Other' => 'Score voor afwezigheid i.v.m. overige redenen',
        'Admin' => 'Gebruikers-ID van Admin-account (standaard: 1)',
        'absenceMax' => 'Maximaal aantal rondes een speler Afwezigheidsscore kan krijgen (excl. Clubafwezigheid)',
        'seasonPart' => 'Aantal rondes per seizoenshelft',
        'save' => 'Sla configuratie op',
    ],
    
    'install' => 'Installeer',

    /*
     *
     * Installed Log translations.
     *
     */
    'installed' => [
        'success_log_message' => 'Laravel Installer successfully INSTALLED on ',
    ],
    /*
     *
     * Final page translations.
     *
     */
    'final' => [
        'title' => 'Technische Installatie Voltooid',
        'finished' => 'Applicatie is succesvol geïnstalleerd.',
        'migration' => 'Migratie Console Meldingen:',
        'console' => 'Applicatie Console Meldingen:',
        'log' => 'Installatie Log:',
        'env' => 'Finaal .env Bestand:',
        'next' => 'Configureer je Admin Account',
    ],
];
