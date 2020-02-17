<?php

namespace TarfinLabs\Netgsm\Exceptions;

use GuzzleHttp\Exception\ClientException;

class CouldNotSendNotification extends \Exception
{
    const MESSAGE_TOO_LONG = 'Mesaj metnindeki bir problemden dolayı gönderilemedi veya standart maksimum mesaj karakter sayısını geçtiniz.';
    const START_DATE_INCORRECT = 'Mesaj gönderim baslangıç tarihinde hata var. Sistem tarihi ile değiştirilip işleme alındı.';
    const END_DATE_INCORRECT = 'Mesaj gönderim sonlandırılma tarihinde hata var. Sistem tarihi ile değiştirilip işleme alındı.Bitiş tarihi başlangıç tarihinden küçük girilmiş ise, sistem bitiş tarihine içinde bulunduğu tarihe 24 saat ekler.';
    const SENDER_INCORRECT = 'Mesaj başlığınız (gönderici adınızın) sistemde tanımlı değil.';
    const CREDENTIALS_INCORRECT = 'Geçersiz kullanıcı adı, şifre veya kullanıcınızın API erişim izni yok.';
    const PARAMETERS_INCORRECT = 'Hatalı sorgulama. Gönderdiğiniz parametrelerden birisi hatalı veya zorunlu alanlardan biri eksik.';
    const RECEIVER_INCORRECT = 'Gönderilen numara hatalı.';
    const OTP_ACCOUNT_NOT_DEFINED = 'Hesabınızda OTP SMS Paketi tanımlı değildir.';
    const QUERY_LIMIT_EXCEED = 'Hesabınızda OTP SMS Paketi tanımlı değildir.';
    const SYSTEM_ERROR = 'Sistem hatası.';
    const NETGSM_GENERAL_ERROR = 'Netgsm responded with an error :';

    /**
     * Thrown when there's a bad request and an error is responded.
     *
     * @param  ClientException  $exception
     *
     * @return static
     */
    public static function NetgsmRespondedWithAnError(ClientException $exception)
    {
        $statusCode = $exception->getResponse()->getStatusCode();
        $description = 'no description given';
        if ($result = json_decode($exception->getResponse()->getBody())) {
            $description = $result->description ?: $description;
        }

        return new static("Netgsm responded with an error `{$statusCode} - {$description}`");
    }
}
