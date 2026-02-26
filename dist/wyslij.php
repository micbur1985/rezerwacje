<?php
// Ten nagłówek mówi Vue.js, że zaraz otrzyma dane w formacie JSON
header('Content-Type: application/json');

// Ukrywamy ewentualne wbudowane błędy PHP, żeby nie popsuć JSONa
error_reporting(0); 

// Załączamy pliki PHPMailer z folderu obok
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Odbieramy to, co wysłał Vue
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($input['name'] ?? '');
    $date = htmlspecialchars($input['date'] ?? '');
    $guests = htmlspecialchars($input['guests'] ?? '');

    $mail = new PHPMailer(true);

    try {
        // --- TWOJE DANE LOGOWANIA DO POCZTY (SMTP) ---
        $mail->isSMTP();
        $mail->Host       = 'mtmspz.nazwa.pl'; // ZMIEŃ TO (np. smtp.gmail.com)
        $mail->SMTPAuth   = true;
        $mail->Username   = 'admin@mtm.waw.pl'; // ZMIEŃ TO (twój adres email)
        $mail->Password   = 'miro321KL!';       // ZMIEŃ TO (twoje hasło)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Użyj ENCRYPTION_STARTTLS jeśli masz port 587
        $mail->Port       = 465; // Zazwyczaj 465 (SMTPS) lub 587 (STARTTLS)
        $mail->CharSet    = 'UTF-8';

        // --- KTO WYSYŁA I DO KOGO ---
        $mail->setFrom('admin@mtm.waw.pl', 'Strona Rezerwacji'); // ZMIEŃ: e-mail z którego wysyłasz
        $mail->addAddress('admin@mtm.waw.pl'); // ZMIEŃ: e-mail na który chcesz dostawać powiadomienia

        // --- TREŚĆ MAILA ---
        $mail->isHTML(false);
        $mail->Subject = 'Nowa rezerwacja od: ' . $name;
        $mail->Body    = "Dane z formularza:\nImię: $name\nData: $date\nGoście: $guests";

        // Wysyłamy!
        $mail->send();
        echo json_encode(['status' => 'success', 'message' => 'Wysłano pomyślnie']);

    } catch (Exception $e) {
        // Jeśli logowanie do SMTP się nie uda, Vue otrzyma poniższy błąd:
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Błąd logowania SMTP. Sprawdź hasło/login w wyslij.php']);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Tylko zapytania POST są dozwolone.']);
}
?>
