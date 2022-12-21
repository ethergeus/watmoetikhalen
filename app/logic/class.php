<?php
define('ERROR_GENERIC', 'Als dit probleem zich blijft voordoen gelieve een mail te sturen naar <a href="mailto:bugs@' . getenv('DOMAIN_PRIMARY') . '">bugs@' . getenv('DOMAIN_PRIMARY') . '</a>. Bij voorbaat dank.');

class Login
{
    private $db_connection = null;
    public $errors = array();
    public $messages = array();
    public function __construct()
    {
        if (!isset($_POST['register']) && !isset($_GET['validate']) && !isset($_POST['forgot-pwd']) && !isset($_POST['pwd-reset'])) {
            if (isset($_POST['login'])) {
                $this->doLogin();
            } elseif (isset($_GET['log-uit'])) {
                $this->doLogout();
            } elseif (isset($_GET['forgot'])) {
                $this->oneClickLogin();
            }
        }
    }
    private function doLogin()
    {
        if (empty($_POST['user_name'])) {
            $this->errors[] = 'Gebruikersnaam is een verplicht veld.';
        } elseif (empty($_POST['user_password'])) {
            $this->errors[] = 'Wachtwoord is een verplicht veld.';
        } else {
            $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if (!$this->db_connection->set_charset('utf8')) {
                $this->errors[] = $this->db_connection->error;
            }
            if (!$this->db_connection->connect_errno) {
                $user_name = $this->db_connection->real_escape_string($_POST['user_name']);
                $sql = "SELECT user_id, user_name, user_email, user_password_hash, user_status
                        FROM users
                        WHERE user_name = '" . $user_name . "' OR user_email = '" . $user_name . "';";
                $result = $this->db_connection->query($sql);
                if ($result->num_rows == 1) {
                    $user = $result->fetch_object();
                    if (password_verify($_POST['user_password'], $user->user_password_hash)) {
                        if ($user->user_status != 0) {
                            $id = intval($user->user_id);
                            $_SESSION['user_id'] = $id;
                            $_SESSION['user_name'] = $user->user_name;
                            $_SESSION['user_email'] = $user->user_email;
                            $_SESSION['logged_in'] = 1;
                            $_SESSION['status'] = intval($user->user_status);
                            load_grades($id);
                            $_SESSION['has_presets'] = 1;
                            $_SESSION['allow_password_change'] = 0;
                        } else {
                            $this->errors[] = 'Je account is nog niet actief. Druk op de link die je in de mail kreeg om je account te activeren.';
                        }
                    } else {
                        $this->errors[] = 'Wachtwoord incorrect.';
                    }
                } else {
                    $this->errors[] = 'Gebruiker bestaat niet.';
                }
            } else {
                $this->errors[] = 'Er was een probleem tijdens het verbinden met de database. ' . ERROR_GENERIC;
            }
        }
    }
    private function oneClickLogin()
    {
        if (isset($_GET['u']) && isset($_GET['v'])) {
            $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if (!$this->db_connection->set_charset('utf8')) {
                $this->errors[] = $this->db_connection->error;
            }
            if (!$this->db_connection->connect_errno) {
                $user_name = $this->db_connection->real_escape_string(strip_tags($_GET['u'], ENT_QUOTES));
                $activation = $this->db_connection->real_escape_string(strip_tags($_GET['v'], ENT_QUOTES));
                $status = 1;
                $sql = "SELECT user_id, user_name, user_email, user_status
                        FROM users
                        WHERE user_name = '" . $user_name . "' AND user_activation = '" . $activation . "' AND user_status = '" . $status . "';";
                $result = $this->db_connection->query($sql);
                if ($result->num_rows == 1) {
                    $user = $result->fetch_object();
                    $user_activation = md5($user_name . '+' . rand(1000, 9999) . '+' . $user_email);
                    $sql = "UPDATE users SET user_activation = '" . $user_activation . "' WHERE user_name = '" . $user_name . "';";
                    $result = $this->db_connection->query($sql);
                    if ($result) {
                        $id = intval($user->user_id);
                        $_SESSION['user_id'] = $id;
                        $_SESSION['user_name'] = $user->user_name;
                        $_SESSION['user_email'] = $user->user_email;
                        $_SESSION['logged_in'] = 1;
                        $_SESSION['status'] = intval($user->user_status);
                        load_grades($id);
                        $_SESSION['has_presets'] = 1;
                        $_SESSION['allow_password_change'] = 1;
                        $this->messages[] = 'Je bent automatisch ingelogd op ' . $user_name . ', je kunt je wachtwoord veranderen.';
                    } else {
                        $this->errors[] = 'Er ging iets mis bij het opnieuw instellen van je account, probeer het later nog eens.';
                    }
                } else {
                    $this->errors[] = 'De verificatiecode is ongeldig of het account is al geactiveerd.';
                }
            } else {
                $this->errors[] = 'Er was een probleem tijdens het verbinden met de database. ' . ERROR_GENERIC;
            }
        }
    }
    public function doLogout()
    {
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        $_SESSION['logged_in'] = 0;
        $this->messages[] = 'Je bent uitgelogd. Aanpassingen aan je cijfers zullen niet langer zichtbaar zijn op andere apparaten.';
    }
    public function displayOutput()
    {
        foreach ($this->errors as $err) {
            echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-12"><div class="alert alert-dismissible alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Log in</h4><p>' . $err . '</p></div></div>';
        }
        foreach ($this->messages as $msg) {
            echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-12"><div class="alert alert-dismissible alert-success"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Log in</h4><p>' . $msg . '</p></div></div>';
        }
    }
}

class Registration
{
    private $db_connection = null;
    public $errors = array();
    public $messages = array();
    public function __construct()
    {
        if (isset($_POST['register'])) {
            $this->registerNewUser();
        } elseif (isset($_GET['validate'])) {
            $this->validateNewUser();
        } elseif (isset($_POST['forgot-pwd'])) {
            $this->forgotPassword();
        } elseif (isset($_POST['pwd-reset'])) {
            $this->changePassword();
        }
    }
    private function registerNewUser()
    {
        if (!validate_recaptcha()) {
            $this->errors[] = 'Volgens ons systeem ben je een robot. Neem dit niet persoonlijk op. Er ging iets mis tijdens het valideren van de reCAPTCHA.';
        } elseif (empty($_POST['user_name'])) {
            $this->errors[] = 'Gebruikersnaam is een verplicht veld.';
        } elseif (empty($_POST['user_password_new']) || empty($_POST['user_password_repeat'])) {
            $this->errors[] = 'Wachtwoord is een verplicht veld.';
        } elseif ($_POST['user_password_new'] !== $_POST['user_password_repeat']) {
            $this->errors[] = 'Wachtwoorden komen niet overeen.';
        } elseif (strlen($_POST['user_password_new']) < 6 || strlen($_POST['user_password_new']) > 20) {
            $this->errors[] = 'Wachtwoord moet minimaal 6 en maximaal 20 karakters bevatten';
        } elseif (strlen($_POST['user_name']) < 3 || strlen($_POST['user_name']) > 12) {
            $this->errors[] = 'Gebruikersnaam moet minimaal 3 en maximaal 12 karakters bevatten.';
        } elseif (!preg_match('/^[a-z\d]{3,12}$/i', $_POST['user_name'])) {
            $this->errors[] = 'Gebruikersnaam kan alleen karakters a-Z en 0-9 bevatten.';
        } elseif (empty($_POST['user_email'])) {
            $this->errors[] = 'Email is een verplicht veld.';
        } elseif (strlen($_POST['user_email']) > 64) {
            $this->errors[] = 'Email adres mag niet meer dan 64 karakters bevatten.';
        } elseif (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Email adres heeft een incorrecte opmaak.';
        } else {
            $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if (!$this->db_connection->set_charset('utf8')) {
                $this->errors[] = $this->db_connection->error;
            }
            if (!$this->db_connection->connect_errno) {
                $user_name = $this->db_connection->real_escape_string(strip_tags($_POST['user_name'], ENT_QUOTES));
                $user_email = $this->db_connection->real_escape_string(strip_tags($_POST['user_email'], ENT_QUOTES));
                $user_password = $_POST['user_password_new'];
                $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT);
                $user_status = 0;
                $user_activation = md5($user_name . '+' . rand(1000, 9999) . '+' . $user_email);
                $sql = "SELECT * FROM users WHERE user_name = '" . $user_name . "' OR user_email = '" . $user_email . "';";
                $result = $this->db_connection->query($sql);
                if ($result->num_rows == 1) {
                    $this->errors[] = 'Sorry, deze gebruikersnaam is al bezet.';
                } else {
                    $sql = "INSERT INTO users (user_name, user_password_hash, user_email, user_status, user_activation)
                            VALUES('" . $user_name . "', '" . $user_password_hash . "', '" . $user_email . "', '" . $user_status . "', '" . $user_activation . "');";
                    $result = $this->db_connection->query($sql);
                    if ($result) {
                        $mail_body = file_get_contents('templates/confirm-email.html');
                        $mail_variables = array('domain_primary' => getenv('DOMAIN_PRIMARY'), 'user_name' => $user_name, 'verify_link' => '?validate&u=' . $user_name . '&v=' . $user_activation);
                        foreach($mail_variables as $key => $value) {
                            $mail_body = str_replace('{{ ' . $key . ' }}', $value, $mail_body);
                        }
                        if (mail($user_email, MAIL_TITLE, quoted_printable_encode($mail_body), MAIL_HEADERS)) {
                            $this->messages[] = 'Een email is verstuurd naar het door jou opgegeven adres. Via de link in de mail kun je je account activeren. Als je de mail niet kunt vinden kijk dan even of deze in je Spam-folder is beland.';
                        } else {
                            $this->errors[] = 'Er was een probleem tijdens het verzenden van een email naar het door jou opgegeven adres. ' . ERROR_GENERIC;
                        }
                    }
                }
            } else {
                $this->errors[] = 'Er was een probleem tijdens het verbinden met de database. ' . ERROR_GENERIC;
            }
        }
    }
    private function validateNewUser()
    {
        if (isset($_GET['u']) && isset($_GET['v'])) {
            $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if (!$this->db_connection->set_charset('utf8')) {
                $this->errors[] = $this->db_connection->error;
            }
            if (!$this->db_connection->connect_errno) {
                $user_name = $this->db_connection->real_escape_string(strip_tags($_GET['u'], ENT_QUOTES));
                $activation = $this->db_connection->real_escape_string(strip_tags($_GET['v'], ENT_QUOTES));
                $status = 0;
                $sql = "SELECT user_email
                        FROM users
                        WHERE user_name = '" . $user_name . "' AND user_activation = '" . $activation . "' AND user_status = '" . $status . "';";
                $result = $this->db_connection->query($sql);
                if ($result->num_rows == 1) {
                    $user = $result->fetch_object();
                    $user_email = $user->user_email;
                    $status = 1;
                    $activation_new = md5($user_name . '+' . rand(1000, 9999) . '+' . $user_email);
                    $sql = "UPDATE users SET user_status = '" . $status . "', user_activation = '" . $activation_new . "' WHERE user_name = '" . $user_name . "' AND user_activation = '" . $activation . "';";
                    $result = $this->db_connection->query($sql);
                    if ($result) {
                        $this->messages[] = 'Je account met de gebruikersnaam ' . $user_name . ' is geactiveerd, je kunt nu inloggen.';
                    } else {
                        $this->errors[] = 'Er ging iets mis bij het activeren van je account, probeer het later nog eens.';
                    }
                } else {
                    $this->errors[] = 'De verificatiecode is ongeldig of het account is al geactiveerd.';
                }
            } else {
                $this->errors[] = 'Er was een probleem tijdens het verbinden met de database. ' . ERROR_GENERIC;
            }
        }
    }
    private function forgotPassword()
    {
        if (!validate_recaptcha()) {
            $this->errors[] = 'Volgens ons systeem ben je een robot. Neem dit niet persoonlijk op. Er ging iets mis tijdens het valideren van de reCAPTCHA.';
        } elseif (isset($_POST['user_name'])) {
            $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if (!$this->db_connection->set_charset('utf8')) {
                $this->errors[] = $this->db_connection->error;
            }
            if (!$this->db_connection->connect_errno) {
                $credentials = $this->db_connection->real_escape_string(strip_tags($_POST['user_name'], ENT_QUOTES));
                $status = 1;
                $sql = "SELECT user_name, user_email, user_activation
                        FROM users
                        WHERE (user_name = '" . $credentials . "' OR user_email = '" . $credentials . "') AND user_status = '" . $status . "';";
                $result = $this->db_connection->query($sql);
                if ($result->num_rows == 1) {
                    $user = $result->fetch_object();
                    $user_name = $user->user_name;
                    $user_email = $user->user_email;
                    $user_activation = $user->user_activation;
                    $mail_variables = array('domain_primary' => getenv('DOMAIN_PRIMARY'), 'user_name' => $user_name, 'verify_link' => '?forgot&u=' . $user_name . '&v=' . $user_activation);
                    $mail_body = file_get_contents('templates/forgot-password.html');
                    foreach($mail_variables as $key => $value) {
                        $mail_body = str_replace('{{ ' . $key . ' }}', $value, $mail_body);
                    }
                    mail($user_email, MAIL_TITLE_RESET_PWD, quoted_printable_encode($mail_body), MAIL_HEADERS);
                }
                $this->messages[] = 'Als dit account bij ons bekend is zal het een email ontvangen met daarin een link om het wachtwoord opnieuw in te stellen.';
            } else {
                $this->errors[] = 'Er was een probleem tijdens het verbinden met de database.';
            }
        }
    }
    private function changePassword()
    {
        if (!validate_recaptcha()) {
            $this->errors[] = 'Volgens ons systeem ben je een robot. Neem dit niet persoonlijk op. Er ging iets mis tijdens het valideren van de reCAPTCHA.';
        } elseif ($_SESSION['logged_in'] != 1) {
            $this->errors[] = 'Je moet ingelogd zijn om je wachtwoord te veranderen. Druk op wachtwoord vergeten om je account opnieuw in te stellen.';
        } elseif (empty($_POST['user_password_old']) && $_SESSION['allow_password_change'] != 1) {
            $this->errors[] = 'Wachtwoord (oud) is een verplicht veld.';
        } elseif (empty($_POST['user_password_new'])) {
            $this->errors[] = 'Wachtwoord (nieuw) is een verplicht veld.';
        } elseif (empty($_POST['user_password_repeat'])) {
            $this->errors[] = 'Wachtwoord (nieuw) herhalen is een verplicht veld.';
        } else {
            $this->db_connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if (!$this->db_connection->set_charset('utf8')) {
                $this->errors[] = $this->db_connection->error;
            }
            if (!$this->db_connection->connect_errno) {
                $user_name = $_SESSION['user_name'];
                $user_email = $_SESSION['user_email'];
                if ($_SESSION['allow_password_change'] == 1) {
                    $allow_pwd_change = true;
                } else {
                    $sql = "SELECT user_password_hash
                            FROM users
                            WHERE user_name = '" . $user_name . "' AND user_email = '" . $user_email . "';";
                    $result = $this->db_connection->query($sql);
                    if ($result->num_rows == 1) {
                        $user = $result->fetch_object();
                        if (password_verify($_POST['user_password_old'], $user->user_password_hash)) {
                            $allow_pwd_change = true;
                        } else {
                            $allow_pwd_change = false;
                            $this->errors[] = 'Wachtwoord incorrect.';
                        }
                    } else {
                        $allow_pwd_change = false;
                        $this->errors[] = 'Er ging iets mis tijdens het aanpassen van je wachtwoord, probeer het later nog eens.';
                    }
                }
                if ($allow_pwd_change) {
                    if ($_POST['user_password_new'] !== $_POST['user_password_repeat']) {
                        $this->errors[] = 'Wachtwoorden komen niet overeen.';
                    } elseif (strlen($_POST['user_password_new']) < 6 || strlen($_POST['user_password_new']) > 20) {
                        $this->errors[] = 'Wachtwoord moet minimaal 6 en maximaal 20 karakters bevatten';
                    } else {
                        $user_password = $_POST['user_password_new'];
                        $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT);
                        $sql = "UPDATE users SET user_password_hash = '" . $user_password_hash . "' WHERE user_name = '" . $user_name . "' AND user_email = '" . $user_email . "';";
                        $result = $this->db_connection->query($sql);
                        if ($result) {
                            $this->messages[] = 'Het wachtwoord van het account ' . $user_name . ' is veranderd.';
                        } else {
                            $this->errors[] = 'Er ging iets mis bij het veranderen van je wachtwoord, probeer het later nog eens.';
                        }
                    }
                }
            } else {
                $this->errors[] = 'Er was een probleem tijdens het verbinden met de database. ' . ERROR_GENERIC;
            }
        }
    }
    public function displayOutput()
    {
        foreach ($this->errors as $err) {
            echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-12"><div class="alert alert-dismissible alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Registratie</h4><p>' . $err . '</p></div></div>';
        }
        foreach ($this->messages as $msg) {
            echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-12"><div class="alert alert-dismissible alert-success"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>Registratie</h4><p>' . $msg . '</p></div></div>';
        }
    }
}
