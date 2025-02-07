<?php
session_save_path('/Users/msaari/sessions/');
session_start();
$_SESSION = array();

require_once 'class-database.php';
require_once 'class-sarjat.php';
require_once 'class-svg.php';
require_once 'class-ykl.php';
require_once 'class-login.php';
require_once 'class-ui.php';
require_once 'functions.php';

$db = new Database();
$sarjat = new Sarjat();
$login = new Login($db);
$ui = new UI($db);

$ui->showHeader();

$login->handleLoginActions();
$login->checkUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csv_file'])) {
        $db->importFromCSV($_FILES['csv_file']['tmp_name']);
    } elseif (isset($_POST['update_book'])) {
    	if (noncePasses($_POST['update_book'])) {
	    	$db->updateBook($_POST['id'], $_POST['first_name'], $_POST['last_name'], $_POST['book_name'], $_POST['url'], $_POST['publisher'], $_POST['translator'], $_POST['additional_info'], $_POST['publication_month'], $_POST['age_recommendation'], $_POST['description'], $_POST['season']);
	        $ui->setStatus('success', "Tiedot päivitetty.");
       	} else {
       		$ui->setStatus('warning', "Nonce ei kelpaa.");
       	}
	} elseif (isset($_POST['first_name'])) {
        $db->insertBook(
        	$_POST['first_name'],
        	$_POST['last_name'],
        	$_POST['book_name'],
        	$_POST['url'],
        	$_POST['publisher'],
        	$_POST['translator'],
        	$sarjat->replaceSeries($_POST['additional_info']),
        	$_POST['publication_month'],
        	$_POST['age_recommendation'],
        	$_POST['description'],
        	$_POST['season']
       	);
       	$ui->setBookValue('publisher', $_POST['publisher']);
       	$ui->setBookValue('season', $_POST['season']);
       	$ui->setStatus('success', "Kirja tallennettu!");
    } elseif (isset($_POST['action']) && $_POST['action'] === 'create_user') {
        $user = $db->getUser($_POST['username']);
        $problem = false;
        if ($user) {
            $ui->setStatus('warning', "Käyttäjänimi on jo varattu.");
            $problem = true;
        }
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $ui->setStatus('warning', "Sähköpostiosoite ei kelpaa.");
        }
        if (!$problem) {
	    	$bytes = openssl_random_pseudo_bytes(12);
			$password = bin2hex($bytes);
			if ($db->insertUser($_POST['username'], $_POST['email'], $password)) {
				$ui->setStatus('success', "Käyttäjä <em>{$_POST['username']}</em> luotu, salasana <em>$password</em>.");
			}
        }
    }
}

if (isset($_GET['export']) && isset($_GET['season'])) {
    $db->exportToCSV($_GET['season']);
    exit();
}

if (isset($_GET['edit']) && !isset($_POST['update_book'])) {
   	$ui->setValues($db->getBook($_GET['edit']));
   	$ui->setBookAction($ui::UPDATE);
}

if (isset($_GET['delete']) && noncePasses($_GET['nonce'])) {
	$book = $db->getBook($_GET['delete']);
	$ui->setStatus('confirm', "Vahvista kirjan <em>{$book['book_name']}</em> poistaminen.");
	$ui->setStatus('action', "delete");
	$ui->setStatus(['id'], $_GET['delete']);
}

if (isset($_GET['confirm']) && $_GET['confirm'] === 'delete' && noncePasses($_GET['nonce'])) {
	$book = $db->getBook($_GET['id']);
	if ($book) {
		$db->deleteBook($_GET['id']);
		$ui->setStatus('success', "<em>{$book['book_name']}</em> poistettiin.");
	} else {
		$ui->setStatus('warning', "Kirjaa ei ole.");
	}
}

$ui->render();
