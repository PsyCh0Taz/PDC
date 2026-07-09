<?php
/**
 * TIR118 - Envoi de mails via SMTP (PHP 5.4, sans dépendance externe)
 */

/**
 * Envoie un mail HTML.
 *
 * @param string $to       Adresse du destinataire
 * @param string $to_name  Nom du destinataire
 * @param string $subject  Sujet
 * @param string $body_html Corps HTML
 * @param string $body_text Corps texte (facultatif)
 * @return bool
 */
function send_mail($to, $to_name, $subject, $body_html, $body_text = '') {
    return _smtp_send(
        MAIL_HOST, MAIL_PORT, MAIL_USER, MAIL_PASS,
        MAIL_FROM, MAIL_FROM_NAME,
        $to, $to_name, $subject, $body_html, $body_text
    );
}

/**
 * Envoi SMTP bas niveau via fsockopen.
 * Supporte STARTTLS (port 587) et AUTH LOGIN.
 */
function _smtp_send($host, $port, $user, $pass, $from, $from_name,
                    $to, $to_name, $subject, $html, $text = '') {
    $errno  = 0;
    $errstr = '';
    $sock   = @fsockopen($host, $port, $errno, $errstr, 10);
    if (!$sock) {
        error_log("TIR118 SMTP : connexion impossible à $host:$port ($errstr)");
        return false;
    }

    stream_set_timeout($sock, 15);

    $read = fgets($sock, 1024);
    if (substr($read, 0, 3) !== '220') {
        fclose($sock);
        return false;
    }

    $ehlo_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

    // EHLO
    fputs($sock, "EHLO $ehlo_host\r\n");
    $caps = '';
    while (($line = fgets($sock, 1024)) !== false) {
        $caps .= $line;
        if (strlen($line) > 3 && $line[3] === ' ') {
            break;
        }
    }

    // STARTTLS sur port 587 ou si annoncé
    if ($port == 587 || strpos($caps, 'STARTTLS') !== false) {
        fputs($sock, "STARTTLS\r\n");
        $resp = fgets($sock, 1024);
        if (substr($resp, 0, 3) === '220') {
            stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            // Re-EHLO après TLS
            fputs($sock, "EHLO $ehlo_host\r\n");
            while (($line = fgets($sock, 1024)) !== false) {
                if (strlen($line) > 3 && $line[3] === ' ') {
                    break;
                }
            }
        }
    }

    // AUTH LOGIN
    fputs($sock, "AUTH LOGIN\r\n");
    fgets($sock, 1024);
    fputs($sock, base64_encode($user) . "\r\n");
    fgets($sock, 1024);
    fputs($sock, base64_encode($pass) . "\r\n");
    $auth_resp = fgets($sock, 1024);
    if (substr($auth_resp, 0, 3) !== '235') {
        fclose($sock);
        error_log("TIR118 SMTP : authentification échouée");
        return false;
    }

    // Enveloppe
    fputs($sock, "MAIL FROM:<$from>\r\n");
    fgets($sock, 1024);
    fputs($sock, "RCPT TO:<$to>\r\n");
    fgets($sock, 1024);
    fputs($sock, "DATA\r\n");
    fgets($sock, 1024);

    // Construction du message MIME multipart
    $boundary = md5(uniqid('tir', true));
    $msg  = 'From: =?UTF-8?B?' . base64_encode($from_name) . "?= <$from>\r\n";
    $msg .= 'To: =?UTF-8?B?' . base64_encode($to_name) . "?= <$to>\r\n";
    $msg .= 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n";
    $msg .= "MIME-Version: 1.0\r\n";
    $msg .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
    $msg .= 'Date: ' . date('r') . "\r\n";
    $msg .= "\r\n";

    if ($text !== '') {
        $msg .= "--$boundary\r\n";
        $msg .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
        $msg .= $text . "\r\n";
    }

    $msg .= "--$boundary\r\n";
    $msg .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    $msg .= $html . "\r\n";
    $msg .= "--$boundary--\r\n";
    $msg .= ".\r\n";

    fputs($sock, $msg);
    $data_resp = fgets($sock, 1024);
    fputs($sock, "QUIT\r\n");
    fclose($sock);

    $ok = (substr($data_resp, 0, 3) === '250');
    if (!$ok) {
        error_log("TIR118 SMTP : DATA refusé ($data_resp)");
    }
    return $ok;
}

// ---------------------------------------------------------------------------
// Templates d'emails
// ---------------------------------------------------------------------------

/** Email de confirmation d'inscription */
function mail_confirmation_inscription($inscription, $tir, $categorie) {
    $nom_prenom  = htmlspecialchars(trim($inscription['prenom'] . ' ' . $inscription['nom']), ENT_QUOTES, 'UTF-8');
    $date_tir    = date('d/m/Y à H:i', strtotime($tir['date_debut']));
    $lien_desins = APP_URL . '/desinscription.php?hash=' . urlencode($inscription['hash']);

    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'
          . '<h2>' . APP_NAME . ' - Confirmation d\'inscription</h2>'
          . "<p>Bonjour $nom_prenom,</p>"
          . '<p>Votre inscription à la séance <strong>' . htmlspecialchars($categorie['titre'], ENT_QUOTES, 'UTF-8') . '</strong>'
          . " du <strong>$date_tir</strong> est confirmée.</p>"
          . '<p>Pour vous désinscrire, cliquez sur le lien ci-dessous :</p>'
          . '<p><a href="' . $lien_desins . '">' . $lien_desins . '</a></p>'
          . '<p>Cordialement,<br>' . APP_NAME . '</p>'
          . '</body></html>';

    $text = "Bonjour $nom_prenom,\n\n"
          . "Votre inscription à la séance \"" . $categorie['titre'] . "\" du $date_tir est confirmée.\n\n"
          . "Pour vous désinscrire : $lien_desins\n\n"
          . "Cordialement,\n" . APP_NAME;

    send_mail(
        $inscription['mail'],
        trim($inscription['prenom'] . ' ' . $inscription['nom']),
        APP_NAME . ' - Confirmation d\'inscription',
        $html,
        $text
    );
}

/** Email de confirmation de désinscription */
function mail_confirmation_desinscription($inscription, $tir, $categorie) {
    $nom_prenom = htmlspecialchars(trim($inscription['prenom'] . ' ' . $inscription['nom']), ENT_QUOTES, 'UTF-8');
    $date_tir   = date('d/m/Y à H:i', strtotime($tir['date_debut']));

    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'
          . '<h2>' . APP_NAME . ' - Désinscription confirmée</h2>'
          . "<p>Bonjour $nom_prenom,</p>"
          . '<p>Votre désinscription de la séance <strong>' . htmlspecialchars($categorie['titre'], ENT_QUOTES, 'UTF-8') . '</strong>'
          . " du <strong>$date_tir</strong> a bien été prise en compte.</p>"
          . '<p>Cordialement,<br>' . APP_NAME . '</p>'
          . '</body></html>';

    $text = "Bonjour $nom_prenom,\n\n"
          . "Votre désinscription de la séance \"" . $categorie['titre'] . "\" du $date_tir a été prise en compte.\n\n"
          . "Cordialement,\n" . APP_NAME;

    send_mail(
        $inscription['mail'],
        trim($inscription['prenom'] . ' ' . $inscription['nom']),
        APP_NAME . ' - Désinscription confirmée',
        $html,
        $text
    );
}

/** Email aux personnes en liste d'attente promouvant leur inscription */
function mail_promotion_attente($inscription, $tir, $categorie) {
    $nom_prenom  = htmlspecialchars(trim($inscription['prenom'] . ' ' . $inscription['nom']), ENT_QUOTES, 'UTF-8');
    $date_tir    = date('d/m/Y à H:i', strtotime($tir['date_debut']));
    $lien_desins = APP_URL . '/desinscription.php?hash=' . urlencode($inscription['hash']);

    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'
          . '<h2>' . APP_NAME . ' - Place disponible !</h2>'
          . "<p>Bonjour $nom_prenom,</p>"
          . '<p>Une place s\'est libérée pour la séance <strong>' . htmlspecialchars($categorie['titre'], ENT_QUOTES, 'UTF-8') . '</strong>'
          . " du <strong>$date_tir</strong>. Vous êtes maintenant inscrit(e).</p>"
          . '<p>Pour vous désinscrire : <a href="' . $lien_desins . '">' . $lien_desins . '</a></p>'
          . '<p>Cordialement,<br>' . APP_NAME . '</p>'
          . '</body></html>';

    send_mail(
        $inscription['mail'],
        trim($inscription['prenom'] . ' ' . $inscription['nom']),
        APP_NAME . ' - Vous êtes inscrit(e) !',
        $html
    );
}
