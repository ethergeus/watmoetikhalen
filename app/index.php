<?php
require_once 'logic/config.php';
require_once 'logic/class.php';
require_once 'logic/fn.php';

session_start();

# Reload grades on refresh
if ($_SESSION['logged_in'] == 1) {
    load_grades($_SESSION['user_id']);
}

$registration = new Registration();
$login = new Login();

if (ALLOW_DEBUG && isset($_GET['forget'])) {
    session_unset();
}

if (!isset($_SESSION['status'])) {
    $_SESSION['niveau'] = STANDAARD_NIVEAU;
    $_SESSION['profiel'] = STANDAARD_PROFIEL;
    $_SESSION['has_presets'] = 0;
    $_SESSION['logged_in'] = 0;
    $_SESSION['status'] = 0;
}

if (isset($_GET['vergeetmij'])) {
    unset($_SESSION['cijfers'], $_SESSION['wegingen'], $_SESSION['punten'], $_SESSION['doelen']);
    if ($_SESSION['logged_in'] == 1) {
        write_grades($_SESSION['user_id']);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords" content="wat,moet,ik,halen,om,te,slagen,eindexamen,cijfers,bereken,middelbare,school,cijfers,examen,toets,tentamen">
    <meta name="description" content="Bereken simpel en snel wat je moet halen om te slagen. Geschikt voor Vwo, Havo en Vmbo.">
    <meta http-equiv="Content-Language" content="nl">
    <link rel="icon" type="image/png" href="/img/grade.png">
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <title>Wat moet ik halen? &middot; De slimme online rekenhulp voor je cijfers</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Bad+Script|Source+Sans+Pro" rel="stylesheet">
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
    <div class="wrapper">
        <?php if ($_SESSION['logged_in'] == 1) : ?>
        <div class="modal modal-pwd-reset fade" id="pwd-reset" role="dialog">
            <div class="model-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Wachtwoord veranderen</h4>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="?">
                            <fieldset>
                                <legend>Het wachtwoord van je account veranderen</legend>
                                <?php if ($_SESSION['allow_password_change'] == 1) : ?>
                                <div class="form-group">
                                    <label for="user_password_new">Wachtwoord (oud)</label>
                                    <input type="password" class="form-control" id="user_password_old" name="user_password_old" placeholder="**********" disabled>
                                    <small class="form-text text-muted">Dit veld hoeft niet ingevuld te worden omdat je via de link in je email adres bent ingelogd.</small>
                                </div>
                                <?php else : ?>
                                <div class="form-group">
                                    <label for="user_password_new">Wachtwoord (oud)</label>
                                    <input type="password" class="form-control" id="user_password_old" name="user_password_old">
                                    <small class="form-text text-muted"></small>
                                </div>
                                <?php endif; ?>
                                <div class="form-group">
                                    <label for="user_password_new">Wachtwoord (nieuw)</label>
                                    <input type="password" class="form-control" id="user_password_new" name="user_password_new">
                                    <small class="form-text text-muted">Kies een veilig wachtwoord.</small>
                                </div>
                                <div class="form-group">
                                    <label for="user_password_repeat">Wachtwoord (nieuw) herhalen</label>
                                    <input type="password" class="form-control" id="user_password_repeat" name="user_password_repeat">
                                </div>
                                <div class="g-recaptcha" data-sitekey="6LfMD0QUAAAAAE49ScQpdznN4EhUvUfM5tjwF9m5"></div>
                                <button type="submit" name="pwd-reset" class="btn btn-success">Wachtwoord veranderen</button>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php else : ?>
        <div class="modal modal-register fade" id="register" role="dialog">
            <div class="model-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Registreer</h4>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="?">
                            <fieldset>
                                <legend>Je cijferlijsten overal en altijd beschikbaar met een account</legend>
                                <div class="form-group">
                                    <label for="user_name">Gebruikersnaam</label>
                                    <input type="text" class="form-control" id="user_name" name="user_name">
                                    <small class="form-text text-muted">Een gebruikersnaam bestaat uit 3 tot 12 letters en cijfers (geen spaties).</small>
                                </div>
                                <div class="form-group">
                                    <label for="user_password_new">Wachtwoord</label>
                                    <input type="password" class="form-control" id="user_password_new" name="user_password_new">
                                    <small class="form-text text-muted">Kies een veilig wachtwoord.</small>
                                </div>
                                <div class="form-group">
                                    <label for="user_password_repeat">Wachtwoord herhalen</label>
                                    <input type="password" class="form-control" id="user_password_repeat" name="user_password_repeat">
                                </div>
                                <div class="form-group">
                                    <label for="user_email">Email adres</label>
                                    <input type="text" class="form-control" id="user_email" name="user_email">
                                    <small class="form-text text-muted">Hier zul je je bevestigingsmail op ontvangen.</small>
                                </div>
                                <div class="g-recaptcha" data-sitekey="6LfMD0QUAAAAAE49ScQpdznN4EhUvUfM5tjwF9m5"></div>
                                <button type="submit" name="register" class="btn btn-success">Registreer</button>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal modal-forgot-pwd fade" id="forgot-pwd" role="dialog">
            <div class="model-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Wachtwoord vergeten?</h4>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="?">
                            <fieldset>
                                <legend>Wachtwoord opnieuw instellen</legend>
                                <div class="form-group">
                                    <label for="user_name">Email of gebruikersnaam</label>
                                    <input type="text" class="form-control" id="user_name" name="user_name">
                                    <small class="form-text text-muted">Als je account bij ons bekend is zul je op het door jouw opgegeven email adres een link toegestuurd krijgen om je wachtwoord opnieuw in te stellen.</small>
                                </div>
                                <div class="g-recaptcha" data-sitekey="6LfMD0QUAAAAAE49ScQpdznN4EhUvUfM5tjwF9m5"></div>
                                <button type="submit" name="forgot-pwd" class="btn btn-success">Opnieuw instellen</button>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#">watmoetikhalen.nl</a>
                </div>
                <div class="collapse navbar-collapse" id="navbar">
                    <ul class="nav navbar-nav">
                        <li class="active"><a href="#">Rekenhulp <span class="sr-only">(je bent hier)</span></a></li>
                    </ul>
                    <?php if ($_SESSION['logged_in'] == 1) : ?>
                    <ul class="nav navbar-nav navbar-right">
                        <li><a href="?log-uit">Log uit</a></li>
                    </ul>
                    <ul class="navbar-form navbar-right">
                        <button type="button" class="btn btn-info pwd-reset-click" data-toggle="modal" data-target="#pwd-reset">Wachtwoord veranderen</button>
                    </ul>
                    <p class="navbar-text navbar-right">Ingelogd als <?php echo $_SESSION['user_name']; ?></p>
                    <?php else : ?>
                    <ul class="navbar-form navbar-right">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#register">Registreer</button>
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#forgot-pwd">Wachtwoord vergeten?</button>
                    </ul>
                    <form class="navbar-form navbar-right" method="post" action="?">
                        <div class="form-group">
                            <input type="text" class="form-control" name="user_name" placeholder="Email of gebruikersnaam">
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" name="user_password" placeholder="Wachtwoord">
                        </div>
                        <button type="submit" name="login" class="btn btn-success">Log in</button>
                    </form>
                    <?php endif ?>
                </div>
            </div>
        </nav>
        <header class="title">
            <div class="title-inner">
                <div class="title-container">
                    <h1>Wat moet ik halen?</h1>
                    <p>De gratis slimme online rekenhulp voor je cijfers</p>
                    <p>Snel en overzichtelijk je cijfers op al je apparaten op een rijtje</p>
                </div>
            </div>
        </header>
        <main class="application">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-lg-3 float-right no-gutter message-board">
                        <?php $login->displayOutput(); ?>
                        <?php $registration->displayOutput(); ?>
                        <?php welcome_user(); ?>
                        <?php handle_user_input(); ?>
                    </div>
                    <div class="col-xs-12 col-lg-9 float-left mark-tool">
                        <div class="panel panel-info">
                            <div class="panel-heading">Cijfer rekenhulp &middot; Vul hier je cijfers en de bijbehorende wegingen in</div>
                                <div class="panel-body"><?php create_content(); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
        </main>
        <footer>
            <div class="container">
                <p>
                    Gemaakt met ❤️ door <a href="https://antonowycz.com">Andrey Antonowycz</a>. <a href="https://award.company/privacy-policy">Privacy policy</a>.
                    Door gebruik te maken van deze website ga je akkoord met de <a href="https://gdpr.eu/cookies">Europese cookiewetgeving</a>.<br>
                    Vragen of suggesties? Mail naar <a href="mailto:contact@watmoetikhalen.nl">contact@watmoetikhalen.nl</a>. Voor bugs kun je terecht bij <a href="mailto:bugs@watmoetikhalen.nl">bugs@watmoetikhalen.nl</a>. We waarderen je hulp! Website icoon gemaakt door <a href="https://www.flaticon.com/authors/freepik" title="Freepik">Freepik</a>.
                </p>
            </div>
        </footer>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js?v=2"></script>
</body>
</html>
