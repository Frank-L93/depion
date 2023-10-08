<?php

return [

    /*
     *
     * Shared translations.
     *
     */
    'title' => 'KeizerPHP Installer',
    'next' => 'Next Step',
    'back' => 'Previous',
    'finish' => 'Install',
    'forms' => [
        'errorTitle' => 'The Following errors occurred:',
    ],

    /*
     *
     * Home page translations.
     *
     */
    'welcome' => [
        'title'   => 'Welcome',
        'message' => 'Thank you for choosing KeizerPHP. If you see this, you will need to follow the installation process. We will first check some requirements, then you will need to fill in some technical information (i.e. Database-connection). Afterwards you will be creating your Admin-account and you configure the competition. If you encounter any issues, contact KeizerPHP via Frank Lambregts (frank@franklambregts.com).',
        'next'    => 'Check Requirements',
    ],

    /*
     *
     * Requirements page translations.
     *
     */
    'requirements' => [
        'title' => 'Step 1 / 5 | Server Requirements',
        'next'    => 'Check Permissions',
    ],

    /*
     *
     * Permissions page translations.
     *
     */
    'permissions' => [
        'title' => 'Step 2 / 5 | Permissions',
        'next' => 'Configure the Technical environment',
    ],

    /*
     *
     * Environment page translations.
     *
     */
    'environment' => [
        'wizard' => [
            'title' => 'Step 3 / 5 | Environment Settings',
            'form' => [
                'name_required' => 'An environment name is required.',
                'app_name_label' => 'App Name',
                'app_name_placeholder' => 'App Name',
                'app_environment_label' => 'App Environment',
                'app_environment_label_local' => 'Local',
                'app_environment_label_developement' => 'Development',
                'app_environment_label_qa' => 'Qa',
                'app_environment_label_production' => 'Production',
                'app_environment_label_other' => 'Other',
                'app_environment_placeholder_other' => 'Enter your environment...',
                'app_debug_label' => 'App Debug',
                'app_debug_label_true' => 'True',
                'app_debug_label_false' => 'False',
                'app_log_level_label' => 'App Log Level',
                'app_log_level_label_debug' => 'debug',
                'app_log_level_label_info' => 'info',
                'app_log_level_label_notice' => 'notice',
                'app_log_level_label_warning' => 'warning',
                'app_log_level_label_error' => 'error',
                'app_log_level_label_critical' => 'critical',
                'app_log_level_label_alert' => 'alert',
                'app_log_level_label_emergency' => 'emergency',
                'app_url_label' => 'App Url',
                'app_url_placeholder' => 'App Url',
                'db_connection_failed' => 'Could not connect to the database.',
                'db_connection_label' => 'Database Connection',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'Database Host',
                'db_host_placeholder' => 'Database Host',
                'db_port_label' => 'Database Port',
                'db_port_placeholder' => 'Database Port',
                'db_name_label' => 'Database Name',
                'db_name_placeholder' => 'Database Name',
                'db_username_label' => 'Database User Name',
                'db_username_placeholder' => 'Database User Name',
                'db_password_label' => 'Database Password',
                'db_password_placeholder' => 'Database Password',

                'app_tabs' => [

                    'mail_label' => 'Mail',
                    'mail_driver_label' => 'Mail Driver',
                    'mail_driver_placeholder' => 'Mail Driver',
                    'mail_host_label' => 'Mail Host',
                    'mail_host_placeholder' => 'Mail Host',
                    'mail_port_label' => 'Mail Port',
                    'mail_port_placeholder' => 'Mail Port',
                    'mail_username_label' => 'Mail Username',
                    'mail_username_placeholder' => 'Mail Username',
                    'mail_password_label' => 'Mail Password',
                    'mail_password_placeholder' => 'Mail Password',
                    'mail_encryption_label' => 'Mail Encryption',
                    'mail_encryption_placeholder' => 'Mail Encryption',
                    'mail_from_label'=>'Mail From Address',
                    'mail_from_placeholder'=>'Mail From Address',

                    'vapid_help_label'=>'Copy the string between the "" for both the public key and private key from the link beneath',
                    'vapid_get_label'=>'Get your VAPID Keys (Necessary to send Push Notifications)',
                    'vapid_public_label'=>'Vapid Public Key',
                    'vapid_public_placeholder'=>'Vapid Public Key',
                    'vapid_private_label'=>'Vapid Private Key',
                    'vapid_private_placeholder'=>'Vapid Private Key',
                ],
                'buttons' => [
                    'setup_database' => 'Setup Database',
                    'setup_application' => 'Setup Application',
                    'install' => 'Install',
                ],
            ],
        ],
        'save'=>'Save .env',
        'success' => 'Your .env file settings have been saved.',
        'errors' => 'Unable to save the .env file, Please create it manually.',
    ],
    'admin' => [
        'title' => 'Step 4 / 5 | Admin Account',
        'name' => 'Name',
        'id' => 'KNSB ID',
        'rating' => 'KNSB Rating',
        'available' => 'Available',
        'email' => 'E-mail Address',
        'password' => 'Password',
        'confirm' => 'Confirm Password',
        'register' => 'Create Admin Account',
    ],
    'configs' =>
    [
        'title' => 'Step 5 / 5 | Competition Configuration',
        'competition' => 'Competition Name',
        'season' => 'Season',
        'endseason' => 'End of Season (default: 0)',
        'start' => 'Start Value (highest)',
        'step' => 'Amount between each value',
        'between' => 'Rounds between a same-pairing',
        'betweenBye' => 'Rounds between being able to be paired to the Bye',
        'Bye' => 'Score for Bye',
        'Presence' => 'Score for being present',
        'Club' => 'Score for being absent due to Clubevent',
        'Personal' => 'Score for being absent due to Personal Circumstances',
        'Other' => 'Score for being absent due to other reasons',
        'Admin' => 'User-ID for Admin (default: 1)',
        'absenceMax' => 'Maximum rounds a player can get Absent-scoring (except Club)',
        'seasonPart' => 'Amount of rounds per season part',
        'save' => 'Save configuration',
    ],

    'install' => 'Install',

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
        'title' => 'Technical Installation Finished',
        'finished' => 'Application has been successfully installed.',
        'migration' => 'Migration & Seed Console Output:',
        'console' => 'Application Console Output:',
        'log' => 'Installation Log Entry:',
        'env' => 'Final .env File:',
        'next' => 'Configure your admin account',
    ],
];
