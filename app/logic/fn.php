<?php
function validate_recaptcha() {
    $response = $_POST['g-recaptcha-response'];
    $data = array('secret' => SECRET, 'response' => $response);
    $options = array('http' => array('method' => 'POST', 'content' => http_build_query($data)));
    $context = stream_context_create($options);
    $verify = file_get_contents(RECAPTCHA_URL, false, $context);
    $captcha_success = json_decode($verify);
    return $captcha_success->success;
}
function write_grades($id) {
    $filename = 'userdata/grades/user' . intval($id) . '.txt';
    $file = fopen($filename, 'w+');
    fwrite($file, grades2json());
    fclose($file);
}
function grades2json() {
    $arr = array();
    foreach (DATA_TYPES as $name) {
        $arr[$name] = $_SESSION[$name];
    }
    return json_encode($arr);
}
function load_grades($id) {
    $filename = 'userdata/grades/user' . intval($id) . '.txt';
    json2grades(file_get_contents($filename));
}
function json2grades($json) {
    $arr = json_decode($json, true);
    foreach (DATA_TYPES as $name) {
        if (isset($arr[$name])) {
            $_SESSION[$name] = $arr[$name];
        } elseif ($name == 'niveau') {
            $_SESSION[$name] = STANDAARD_NIVEAU;
        } elseif ($name == 'profiel') {
            $_SESSION[$name] = STANDAARD_PROFIEL;
        } else {
            $_SESSION[$name] = array();
        }
    }
}
function welcome_user() {
    if (empty($_POST) && empty($_GET)) {
        if (!isset($_COOKIE['welcome_user_closed'])) {
            echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-12"><div class="alert alert-dismissible alert-success"><button type="button" class="close close-welcome-user" data-dismiss="alert">&times;</button><h4>Welkom</h4>';
            echo '<p>Op deze website kun je gemakkelijk en snel uitrekenen welke cijfers je nog moet halen om een bepaald gemiddelde te staan. Kies je niveau, vakkenpakket en vul je cijferlijst in op direct te beginnen.</p>';
            echo '</div></div>';
        }
        if (!isset($_COOKIE['account_info_closed'])) {
            echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-12"><div class="alert alert-dismissible alert-success"><button type="button" class="close close-account-info" data-dismiss="alert">&times;</button><h4>Je cijfers overal?</h4>';
            echo '<p>Maak een account aan om je cijfers altijd en overal beschikbaar te hebben.</p>';
            echo '<p>Je cijfers worden opgeslagen zodat je niet alles steeds opnieuw hoeft in te vullen. Je kunt op de knop "Vergeet mij" onderaan de pagina drukken om je cijfers te verwijderen.</p>';
            echo '</div></div>';
        }
    if (!isset($_COOKIE['gratiscv_promo_closed'])) {
            echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-12"><div class="alert alert-dismissible alert-success"><button type="button" class="close close-gratiscv-promo" data-dismiss="alert">&times;</button><h4>Bijbaantje?</h4>';
            echo '<p>Zoek je een bijbaantje maar heb je nog geen CV?</p>';
            echo '<p>Op <a class="btn btn-xs btn-info" href="https://gratiscv.com">gratiscv.com</a> kun je eenvoudig en volledig gratis een CV in elkaar zetten en downloaden. Gemaakt door studenten, voor studenten.</p>';
            echo '</div></div>';
        }
    }
}
function handle_user_input() {
    echo '<div class="application-output">';
    if (isset($_POST['niveau']) && isset($_POST['profiel']) && array_key_exists($_POST['niveau'], TREE)
        && array_key_exists($_POST['profiel'], TREE[$_POST['niveau']]['profiles'])) {
        unset($_SESSION['cijfers'], $_SESSION['wegingen'], $_SESSION['punten'], $_SESSION['doelen']);
        if (ALLOW_DEBUG && isset($_GET['debug'])) {
            echo '<h3>$_POST</h3><pre>';
            var_dump($_POST);
            echo '</pre>';
        }
        $_SESSION['niveau'] = $_POST['niveau'];
        $_SESSION['profiel'] = $_POST['profiel'];
        $_SESSION['has_presets'] = 1;
        $_SESSION['vakken'] = array();
        $_SESSION['cijfers'] = array();
        $_SESSION['wegingen'] = array();
        $_SESSION['punten'] = array();
        $_SESSION['doelen'] = array();
        $subjects = is_array($_POST['vakken']) ? $_POST['vakken'] : array();
        $grades = is_array($_POST['cijfers']) ? $_POST['cijfers'] : array();
        $weights = is_array($_POST['wegingen']) ? $_POST['wegingen'] : array();
        $points = isset($_POST['punten']) ? $_POST['punten'] : array();
        $goals = isset($_POST['doelen']) ? $_POST['doelen'] : array();
        for ($i = 0; $i < count($subjects); $i++) {
            $_SESSION['cijfers'][$i] = array();
            $_SESSION['wegingen'][$i] = array();
            $subj_id = intval($subjects[$i]);
            $_SESSION['vakken'][$i] = $subj_id;
            $subj = isset($subjects[$i]) && is_int(intval($subjects[$i])) ? SUBJECTS[$subj_id] : '';
            $subj_grades = isset($grades[$i]) ? $grades[$i] : array();
            $subj_weights = isset($weights[$i]) ? $weights[$i] : array();
            $total_weight = 0; $average = 0; $feedback = false;
            for ($j = 0; $j < count($subj_grades); $j++) {
                $grade = isset($subj_grades[$j]) ? round(floatval(str_replace(',', '.', $subj_grades[$j])), 2) : 0;
                $weight = isset($subj_weights[$j]) ? round(floatval(str_replace(',', '.', $subj_weights[$j])), 2) : POINTS;
                if ($grade != 0 && abs($grade - 5) <= 5) {
                    if (abs($weight - MAX_WEIGHT) >= MAX_WEIGHT) {
                        $weight = POINTS;
                    }
                    if ($subj_id > 1 || ALLOW_DEBUG && isset($_GET['debug'])) {
                        $feedback = true;
                    }
                    $average += $grade * $weight;
                    $total_weight += $weight;
                    $_SESSION['cijfers'][$i][] = $grade;
                    $_SESSION['wegingen'][$i][] = $weight;
                }
            }
            if (ALLOW_DEBUG && isset($_GET['debug'])) {
                echo '<h3>' . $subj . '</h3><h4>Grades</h4><pre>';
                var_dump($subj_grades);
                echo '</pre><h4>Weights</h4><pre>';
                var_dump($subj_weights);
                echo '</pre>';
            }
            if ($feedback) {
                $current = round($average / $total_weight, 2);
                $subj_points = isset($points[$i]) && floatval(str_replace(',', '.', $points[$i])) != 0 ? round(floatval(str_replace(',', '.', $points[$i])), 2) : POINTS;
                $subj_goal = isset($goals[$i]) && floatval(str_replace(',', '.', $goals[$i])) != 0 ? round(floatval(str_replace(',', '.', $goals[$i])), 2) : GOAL;
                $x = round(($subj_goal * ($total_weight + $subj_points) - $average) / $subj_points, 2);
                $_SESSION['punten'][] = $subj_points;
                $_SESSION['doelen'][] = $subj_goal;
                echo '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-12"><div class="alert alert-dismissible ';
                if ($current >= $x) {
                    echo 'alert-success';
                } elseif ($x <= 10) {
                    echo 'alert-warning';
                } else {
                    echo 'alert-danger';
                }
                echo '"><button type="button" class="close" data-dismiss="alert">&times;</button><h4>' . $subj . '</h4><p>';
                if ($current >= $x) {
                    echo 'Ziet er goed uit!';
                } elseif ($x <= 10) {
                    echo 'Je bent goed op weg!';
                } else {
                    echo 'Oei, ziet er niet al te best uit!';
                }
                echo '</p><p>Je staat momenteel een <kbd>' . $current . '</kbd>, je zult een <kbd>' . $x . '</kbd> moeten halen om gemiddeld een <kbd>' . $subj_goal . '</kbd> te staan.</p></div></div>';
            } else {
                $_SESSION['punten'][] = POINTS;
                $_SESSION['doelen'][] = GOAL;
            }
        }
        if ($_SESSION['logged_in'] == 1) {
            write_grades($_SESSION['user_id']);
        }
    }
    echo '</div>';
}
function create_content() {
    if (ALLOW_DEBUG && isset($_GET['debug'])) {
        echo '<h3>$_SESSION</h3><pre>';
        var_dump($_SESSION);
        echo '</pre>';
    }
    echo '<ul class="nav nav-tabs">';
    foreach (TREE as $niveau=>$inhoud) {
        echo '<li';
        if ($_SESSION['niveau'] == $niveau) {
            echo ' class="active"';
        } elseif (sizeof(TREE[$niveau]['profiles']) == 0) {
            echo ' class="disabled"';
        }
        echo '><a href="#' . $niveau . '" data-toggle="tab">' . $inhoud['name'] . '</a></li>';
    }
    echo '</ul><div class="tab-content">';
    foreach (TREE as $niveau=>$inhoud) {
        if (sizeof(TREE[$niveau]['profiles']) != 0) {
            create_tab($niveau);
        }
    }
    echo '</div>';
}
function create_tab($niveau) {
    $profielen = TREE[$niveau]['profiles'];
    echo '<div class="niveau tab-pane fade';
    if ($_SESSION['niveau'] == $niveau) {
        echo ' active in';
    }
    echo '" id="' . $niveau . '"><div class="profielen float-right col-xs-12 col-sm-3"><div class="list-group table-of-contents">';
    foreach ($profielen as $profiel=>$inhoud) {
        echo '<a class="list-group-item';
        if ($_SESSION['niveau'] == $niveau && $_SESSION['profiel'] == $profiel
            || $_SESSION['niveau'] != $niveau && $profiel == TREE[$niveau]['default_profile']) {
            echo ' active';
        }
        echo '" href="#' . $niveau . '-' . $profiel . '" data-toggle="tab">' . TREE[$niveau]['profiles'][$profiel]['name'] . '</a>';
    }
    echo '</div></div><div class="tab-content float-left col-xs-12 col-sm-9">';
    foreach ($profielen as $profiel=>$inhoud) {
        profiel($niveau, $profiel, $inhoud);
    }
    echo '</div></div>';
}
function profiel($niveau, $profiel, $inhoud) {
    $presets = false;
    $autofill = false;
    echo '<div class="tab-pane';
    if ($_SESSION['niveau'] == $niveau && $_SESSION['profiel'] == $profiel
        || $_SESSION['niveau'] != $niveau && $profiel == TREE[$niveau]['default_profile']) {
        echo ' active';
        if ($_SESSION['niveau'] == $niveau) {
            $autofill = true;
        }
        if (boolval($_SESSION['has_presets'])) {
            $presets = true;
        }
    }
    echo '" id="' . $niveau . '-' . $profiel . '">';
    $vakken = $inhoud['subjects'];
    echo '<div class="vakkenpakket ' . $niveau . ' ' . $profiel . '"><form class="form-horizontal" method="post" action="?"><fieldset>';
    echo '<input type="hidden" name="niveau" value="' . $niveau . '"><input type="hidden" name="profiel" value="' . $profiel . '">';
    $n = 0;
    for ($i = 0; $i < count($vakken); $i++) {
        if (is_string($vakken[$i])) {
            echo '<legend>' . $vakken[$i] . '</legend>';
        } else {
            echo '<div class="vak row">';
            $selected = $presets && isset($_SESSION['vakken'][$n]) && $autofill ? $_SESSION['vakken'][$n] : 0;
            $autofill_grades = $presets && isset($_SESSION['cijfers'][$n]) && $autofill ? $_SESSION['cijfers'][$n] : array();
            $autofill_weights = $presets && isset($_SESSION['wegingen'][$n]) && $autofill ? $_SESSION['wegingen'][$n] : array();
            $points = $presets && isset($_SESSION['punten'][$n]) && $autofill ? $_SESSION['punten'][$n] : POINTS;
            $goal = $presets && isset($_SESSION['doelen'][$n]) && $autofill ? $_SESSION['doelen'][$n] : GOAL;
            subject($vakken[$i], $selected);
            grades($n, $autofill_grades, $autofill_weights, $selected);
            $n++;
            settings($points, $goal, $selected);
            echo '</div>';
        }
    }
    echo '<footer class="form-options"><div class="form-group col-lg-12">';
    echo '<a class="btn btn-forget btn-danger" href="?vergeetmij"><span class="glyphicon glyphicon-fire" aria-hidden="true"></span> Cijfers vergeten</a>';
    echo '<button class="btn btn-reset btn-warning" type="reset"><span class="glyphicon glyphicon-erase" aria-hidden="true"></span> Terugzetten</button>';
    echo '<button class="btn btn-submit btn-success" type="submit"><span class="glyphicon glyphicon-blackboard" aria-hidden="true"></span> Bereken</button>';
    echo '</div></footer></fieldset></form></div></div>';
}
function subject($options, $selected) {
    echo '<div class="vakkeuze col-xs-5 col-sm-3">';
    if (is_array($options)) {
        echo '<select class="form-control input-sm" name="vakken[]">';
        foreach ($options as $subject_id) {
            if ($subject_id != 0 || $selected == 0) {
                echo '<option value="' . $subject_id . '"';
                if ($selected == $subject_id) {
                    echo ' selected';
                }
                echo '>' . SUBJECTS[$subject_id] . '</option>';
            }
        }
        echo '</select>';
    } else {
        echo '<input class="form-control input-sm" type="text" value="' . SUBJECTS[$options] . '" readonly>';
        echo '<input type="hidden" name="vakken[]" value="' . $options . '">';
    }
    echo '<button class="btn btn-remove btn-warning btn-sm" href="#"';
    if ($selected == 1) {
        echo ' style="display: none;"';
    }
    echo '><span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>';
    echo '<button class="btn btn-add btn-success btn-sm" href="#"';
    if ($selected == 1) {
        echo ' style="display: none;"';
    }
    echo '><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>';
    echo '</div>';
}
function grades($n, $autofill_grades, $autofill_weights, $selected) {
    echo '<div class="cijfers col-xs-7 col-sm-6"';
    if ($selected == 1) {
        echo ' style="display: none;"';
    }
    echo '>';
    for ($i = 0; $i < max(GRADE_COLS, count($autofill_grades)); $i++) {
        $grade = isset($autofill_grades[$i]) ? $autofill_grades[$i] : '';
        $weight = isset($autofill_weights[$i]) ? $autofill_weights[$i] : '';
        echo '<div class="cijfer form-group form-group-sm">';
        echo '<input class="form-control" type="text" name="cijfers[' . $n . '][]" maxlength="4" placeholder="' . GRADE . '" value="' . $grade . '">';
        echo '<input class="form-control" type="text" name="wegingen[' . $n . '][]" maxlength="4" placeholder="' . POINTS . '" value="' . $weight . '">';
        echo '</div>';
    }
    echo '</div>';
}
function settings($points, $goal, $selected) {
    echo '<div class="opties col-xs-12 col-sm-3"';
    if ($selected == 1) {
        echo ' style="display: none;"';
    }
    echo '><div class="col-xs-6 col-sm-12"><div class="punten form-group form-group-sm">';
    echo '<label class="control-label">Eindcijfer doel</label><input class="form-control" type="text" name="doelen[]" maxlength="4" placeholder="' . GOAL . '" value="' . $goal . '">';
    echo '</div></div><div class="col-xs-6 col-sm-12"><div class="doel form-group form-group-sm">';
    echo '<label class="control-label">Resterende punten</label><input class="form-control" type="text" name="punten[]" maxlength="4" placeholder="' . POINTS . '" value="' . $points . '">';
    echo '</div></div></div>';
}
