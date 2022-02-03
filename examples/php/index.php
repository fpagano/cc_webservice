<?php

/**
  Esempio di utilizzo delle API di Camera di Compensazione per l'invio delle fatture da compensare
 */
$ch = creaCurl('https://webapp.cameracompensazione.it/webservices/');
$jwt = getJwt($ch);
if ($jwt != null) {
    require "dati.php"; // contiene i dati di prova
    foreach ($elencoFatture as $fattura) {
        sendFattura($ch, $jwt, $fattura["tipo_fattura"], $fattura["nome_file"], base64_encode($fattura["contenuto"]), $fattura["residuo"]);
    }
}
curl_close($ch);

function sendFattura($ch, $jwt, $tipoFattura, $nomeFile, $cont_b64, $residuo) {
    $jsonOp = <<<EOD
        {
            "op": "ins_dati",
            "jwt": "$jwt",
            "dati": {
                "codProvenienza":null,
                "tipo_fattura": "$tipoFattura",
                "nome_file": "$nomeFile",
                "documento_base64": "$cont_b64",
                "importo_residuo": "$residuo"
            }
        }
EOD;
//                echo($jsonOp);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonOp);
    $response = curl_exec($ch);
    $risposta = json_decode($response);
//                var_dump($risposta);

    $esito = $risposta->result;
    $errore = $risposta->message;
    echo "$nomeFile: ";
    if ($esito == "ok") {
        echo "correttamente inviato<br>n";
    } else {
        echo $errore . "<br>\n";
    }
}

function getJwt($ch) {
    /** questo è l'account di connessione, da chiedere a commerciale@cameracompensazione.it */
    $codAffiliato = "test_environment";
    $token = "sxFSCQJHVuilDVvWswGYLlirm2L6TZc1";
    /**/

    $jsonLogin = <<<EOD
        {
            "op":"gjwt",
            "dati":{
                "cod_affiliato":"$codAffiliato",
                "token":"$token"
            }
        }
EOD;
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonLogin);

    $response = curl_exec($ch);
    if ($response === false) {
        echo '<p>Curl error: ' . curl_error($ch) . "</p>";
        return null;
    } else {
        print "<p>Connesso con Camera di Compensazione</p>";
        $risposta = json_decode($response);
        $jwt = $risposta->jwt;
        return $jwt;
    }
}

function creaCurl($url) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // per i certificati autofirmati
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    return $ch;
}
