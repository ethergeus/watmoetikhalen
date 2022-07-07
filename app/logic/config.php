<?php
# Developer options
define('ALLOW_DEBUG', false);
define('REDIRECT_URL', 'https://' . $_ENV['DOMAIN_PRIMARY']);
define('SECRET', $_ENV['RECAPTCHA_SECRET']);
define('RECAPTCHA_URL', 'https://www.google.com/recaptcha/api/siteverify');

# Database credentials from process environment and docker config
define('DB_HOST', 'database');
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);

# Automatic mail options
define('MAIL_TITLE', 'Je bent bijna klaar met registreren bij ' . $_ENV['DOMAIN_PRIMARY']);
define('MAIL_TITLE_RESET_PWD', 'Je account bij ' . $_ENV['DOMAIN_PRIMARY'] . ' opnieuw instellen');
define('MAIL_HEADERS', 'From: "Wat moet ik halen" <noreply@' . $_ENV['DOMAIN_PRIMARY'] . '>' . "\r\n" .
                       'Reply-To: contact@' . $_ENV['DOMAIN_PRIMARY'] . "\r\n" .
                       'X-Mailer: PHP/' . phpversion() . "\r\n" .
                       'Content-type: text/html; charset=iso-8859-1');

# Subjects
define('SUBJECTS', array(
    'Kies een vak', 'Geen extra vak',
    'Nederlands', 'Engels', 'Wiskunde',
    'Duits', 'Frans', 'Fries', 'Grieks', 'Italiaans', 'Latijn', 'Russisch', 'Spaans',
    'Biologie', 'Informatica', 'Natuurkunde', 'Natuur, leven en technologie', 'Scheikunde', 'Wiskunde D',
    'Aardrijkskunde', 'Economie', 'Filosofie', 'Geschiedenis', 'Kunst', 'Maatschappijwetenschappen', 'Management en organisatie',
    'Natuur- en scheikunde 1', 'Natuur- en scheikunde 2', 'Maatschappijleer 1', 'Maatschappijleer 2', 'Kunstvakken 1', 'Kunstvakken 2', 'Technologie'
));

# Default focus
define('STANDAARD_NIVEAU', 'havo');
define('STANDAARD_PROFIEL', 'cm');

# Grade storing
define('DATA_TYPES', array('niveau', 'profiel', 'vakken', 'cijfers', 'wegingen', 'punten', 'doelen'));

# Application defaults
define('GRADE_COLS', 3);
define('MAX_WEIGHT', 1024);
define('GRADE', '?');
define('POINTS', 1);
define('GOAL', 7);

# Options map
define('TREE', array(
    'vmbo'=>array('name'=>'Vmbo', 'default_profile'=>'nt',
        'profiles'=>array(
            'nt'=>array('name'=>'Sector Techniek', 'subjects'=>array('Gemeenschappelijke deel', 2, 3, 28, 30,
                'Sectordeel', 4, 26,
                'Theoretische leerweg', array(0, 1, 27, 13, 29, 22, 19, 20, 6, 5, 12, 7, 31, 32), array(0, 1, 27, 13, 29, 22, 19, 20, 6, 5, 12, 7, 31, 32)
            )),
            'ng'=>array('name'=>'Sector Zorg &amp; Welzijn', 'subjects'=>array('Gemeenschappelijke deel', 2, 3, 28, 30,
                'Sectordeel', 13, array(4, 19, 22, 29),
                'Theoretische leerweg', array(0, 1, 4, 26, 27, 29, 22, 19, 20, 6, 5, 12, 7, 31, 32), array(0, 1, 4, 26, 27, 29, 22, 19, 20, 6, 5, 12, 7, 31, 32)
            )),
            'em'=>array('name'=>'Sector Economie', 'subjects'=>array('Gemeenschappelijke deel', 2, 3, 28, 30,
                'Sectordeel', 20, array(5, 12, 4),
                'Theoretische leerweg', array(0, 1, 4, 26, 27, 13, 29, 22, 19, 6, 5, 12, 7, 31, 32), array(0, 1, 4, 26, 27, 13, 29, 22, 19, 6, 5, 12, 7, 31, 32)
            )),
            'cm'=>array('name'=>'Sector Landbouw', 'subjects'=>array('Gemeenschappelijke deel', 2, 3, 28, 30,
                'Sectordeel', 4, array(26, 13),
                'Theoretische leerweg', array(0, 1, 26, 27, 13, 29, 22, 19, 20, 6, 5, 12, 7, 31, 32), array(0, 1, 26, 27, 13, 29, 22, 19, 20, 6, 5, 12, 7, 31, 32)
            ))
        )
    ),
    'havo'=>array('name'=>'Havo', 'default_profile'=>'cm',
        'profiles'=>array(
            'cm'=>array('name'=>'Cultuur &amp; Maatschappij', 'subjects'=>array('Gemeenschappelijke deel', 2, 3,
                'Profielvakken', 22, array(0, 5, 6, 7, 9, 11, 12), array(0, 23, 21, 5, 6, 7, 9, 11, 12), array(0, 19, 24, 20),
                'Vrije deel', array(0, 19, 13, 20, 21, 14, 23, 25, 5, 6, 7, 9, 11, 12, 15, 17, 18), array(0, 1, 4, 5, 6, 7, 9, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25)
            )),
            'em'=>array('name'=>'Economie &amp; Maatschappij', 'subjects'=>array('Gemeenschappelijke deel', 2, 3,
                'Profielvakken', 4, 20, 22, array(0, 25, 19, 24, 5, 6, 7, 9, 11, 12),
                'Vrije deel', array(0, 19, 13, 20, 21, 14, 23, 25, 5, 6, 7, 9, 11, 12, 15, 17, 18), array(0, 1, 4, 5, 6, 7, 9, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25)
            )),
            'ng'=>array('name'=>'Natuur &amp; Gezondheid', 'subjects'=>array('Gemeenschappelijke deel', 2, 3,
                'Profielvakken', 4, 13, 17, array(0, 16, 19, 15),
                'Vrije deel', array(0, 19, 13, 20, 21, 14, 23, 25, 5, 6, 7, 9, 11, 12, 15, 17, 18), array(0, 1, 4, 5, 6, 7, 9, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25)
            )),
            'nt'=>array('name'=>'Natuur &amp; Techniek', 'subjects'=>array('Gemeenschappelijke deel', 2, 3,
                'Profielvakken', 4, 15, 17, array(0, 16, 14, 18),
                'Vrije deel', array(0, 19, 13, 20, 21, 14, 23, 25, 5, 6, 7, 9, 11, 12, 15, 17, 18), array(0, 1, 4, 5, 6, 7, 9, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25)
            ))
        )
    ),
    'vwo'=>array('name'=>'Vwo', 'default_profile'=>'cm',
        'profiles'=>array(
            'cm'=>array('name'=>'Cultuur &amp; Maatschappij', 'subjects'=>array('Gemeenschappelijke deel', 2, 3, array(0, 5, 6, 7, 9, 11, 12, 8, 10), 4,
                'Profielvakken', 22, array(0, 23, 21, 5, 6, 7, 9, 11, 12, 8, 10), array(0, 19, 24, 20),
                'Vrije deel', array(0, 19, 13, 20, 21, 14, 23, 25, 5, 6, 7, 9, 11, 12, 15, 17, 18, 8, 10), array(0, 1, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25)
            )),
            'em'=>array('name'=>'Economie &amp; Maatschappij', 'subjects'=>array('Gemeenschappelijke deel', 2, 3, array(0, 5, 6, 7, 9, 11, 12, 8, 10), 4,
                'Profielvakken', 4, 20, 22, array(0, 25, 19, 24, 5, 6, 7, 9, 11, 12, 8, 10),
                'Vrije deel', array(0, 19, 13, 20, 21, 14, 23, 25, 5, 6, 7, 9, 11, 12, 15, 17, 18, 8, 10), array(0, 1, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25)
            )),
            'ng'=>array('name'=>'Natuur &amp; Gezondheid', 'subjects'=>array('Gemeenschappelijke deel', 2, 3, array(0, 5, 6, 7, 9, 11, 12, 8, 10), 4,
                'Profielvakken', 4, 13, 17, array(0, 16, 19, 15),
                'Vrije deel', array(0, 19, 13, 20, 21, 14, 23, 25, 5, 6, 7, 9, 11, 12, 15, 17, 18, 8, 10), array(0, 1, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25)
            )),
            'nt'=>array('name'=>'Natuur &amp; Techniek', 'subjects'=>array('Gemeenschappelijke deel', 2, 3, array(0, 5, 6, 7, 9, 11, 12, 8, 10), 4,
                'Profielvakken', 4, 15, 17, array(0, 16, 14, 18),
                'Vrije deel', array(0, 19, 13, 20, 21, 14, 23, 25, 5, 6, 7, 9, 11, 12, 15, 17, 18, 8, 10), array(0, 1, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25)
            ))
        )
    )
));
