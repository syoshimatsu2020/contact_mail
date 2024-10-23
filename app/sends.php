<?php
    $contact_type_array = [];
    $contact_type;
    $result = [];

    // jsonに変換するクラスをインクルード
    include('./class/OutputJson.php');

    $output = new OutputJson;

    // POSTデータに、「'」、「"」、「&」、「<」、「>」があれば変換
    foreach($_POST['contact-type'] as $contact_type) {
        array_push($contact_type_array, htmlspecialchars($contact_type, ENT_QUOTES));
        $contact_type .=  htmlspecialchars($contact_type, ENT_QUOTES) . ' ';
    }
    $name = htmlspecialchars($_POST['p-name'], ENT_QUOTES);
    $name_kana = htmlspecialchars($_POST['name-kana'], ENT_QUOTES);
    $tell = htmlspecialchars($_POST['tell'], ENT_QUOTES);
    $mail = htmlspecialchars($_POST['mail'], ENT_QUOTES);
    $post_code = htmlspecialchars($_POST['post-code'], ENT_QUOTES);
    $prefectures = htmlspecialchars($_POST['prefectures'], ENT_QUOTES);
    $municipalities = htmlspecialchars($_POST['municipalities'], ENT_QUOTES);
    $others = htmlspecialchars($_POST['others'], ENT_QUOTES);
    $remarks = htmlspecialchars($_POST['remarks'], ENT_QUOTES);

    $error_status = true;

    if (empty($contact_type_array)) {
        $error_status = false;
    }

    if (!(isset($name)) || empty($name)) {
        $error_status = false;
    }

    if (!(isset($name_kana)) || empty($name_kana)) {
        $error_status = false;
    }

    $pattern = '/^0[0-9]{9,10}\z/';
    if (!(isset($tell)) || empty($tell)) {
        $error_status = false;
    }else if (!preg_match($pattern, $tell)) {
        $error_status = false;
    }

    if (!(isset($mail)) || empty($mail)) {
        $error_status = false;
    } elseif (!(filter_var($mail, FILTER_VALIDATE_EMAIL))) {
        $error_status = false;
    }

    if (!(isset($post_code)) || empty($post_code)) {
        $error_status = false;
    }

    if (!(isset($prefectures)) || empty($prefectures)) {
        $error_status = false;
    }

    if (!(isset($municipalities)) || empty($municipalities)) {
        $error_status = false;
    }

    if (!(isset($others)) || empty($others)) {
        $error_status = false;
    }

    if (!(isset($remarks)) || empty($remarks)) {
        $error_status = false;
    }

    // 未入力等があれば終了
    if (!$error_status) {
        $result['status'] = 400;
        $result['massage'] = '未入力があります';

        return $output->output($result);
    }

    $address = '〒' . $post_code . ' ' . $prefectures . $municipalities . $others;

    require './admin/app_mail_const.php';

    // 管理者へメール送信
    // メール本文を生成
    ob_start();

    include('./mail_temp/mailbody_admin.tmp.php');
    $mail_body = ob_get_contents();

    ob_end_clean();

    // 送り先
    $to = MAIL_TO;
    // 件名
    $subject = SUBJECT1;
    // メール本文の改行を変換
    $mail_body = strtr( $mail_body, [ "\r\n" => "\n", "\r" => "\n" ] );
    // ヘッダー
    $headers = [
        'MIME-Version' => '1.0',
        'Content-Transfer-Encoding' => '7bit',
        'Return-Path' => 'contact@fwebprod.com',
        'From' => mb_encode_mimeheader( MAIL_ORGANIZATION ) . MAIL_FROM,
        'Sender' => mb_encode_mimeheader( MAIL_ORGANIZATION ) . MAIL_FROM,
        'Reply-To' => mb_encode_mimeheader( MAIL_ORGANIZATION ) . MAIL_FROM,
        'Organization' => mb_encode_mimeheader( MAIL_ORGANIZATION ),
        'X-Sender' => MAIL_ADDRESS,
        'X-Priority' => '1',
    ];

    // メールクラスをインクルードして送信
    include('./class/Mail.php');

    $response = new Mail;

    $result['status'] = 200;
    $result['massage'] = '送信完了';

    if (!$response->send($headers, $to, $subject, $mail_body)) {
        $result['status'] = 500;
        $result['massage'] = '送信失敗';

        return $output->output($result);
    }

    // 問い合わせ者へメール送信
    // メール本文を生成
    ob_start();

    include('./mail_temp/mailbody_inquirer.tmp.php');
    $mail_body = ob_get_contents();

    ob_end_clean();

    // 送り先
    $to = $mail;
    // 件名
    $subject = SUBJECT2;
    // メール本文の改行を変換
    $mail_body = strtr( $mail_body, [ "\r\n" => "\n", "\r" => "\n" ] );

    // メール送信
    if (!$response->send($headers, $to, $subject, $mail_body)) {
        $result['status'] = 500;
        $result['massage'] = '送信失敗';

        return $output->output($result);
    }

    return $output->output($result);
?>