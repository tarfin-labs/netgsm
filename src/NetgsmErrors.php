<?php

namespace TarfinLabs\Netgsm;

/**
 * Class NetgsmErrors.
 */
class NetgsmErrors
{
    const MESSAGE_TOO_LONG        = 'netgsm::errors.message_too_long';
    const START_DATE_INCORRECT    = 'netgsm::errors.start_date_incorrect';
    const END_DATE_INCORRECT      = 'netgsm::errors.end_date_incorrect';
    const SENDER_INCORRECT        = 'netgsm::errors.sender_incorrect';
    const CREDENTIALS_INCORRECT   = 'netgsm::errors.credentials_incorrect';
    const PARAMETERS_INCORRECT    = 'netgsm::errors.parameters_incorrect';
    const RECEIVER_INCORRECT      = 'netgsm::errors.receiver_incorrect';
    const OTP_ACCOUNT_NOT_DEFINED = 'netgsm::errors.otp_account_not_defined';
    const QUERY_LIMIT_EXCEED      = 'netgsm::errors.query_limit_exceed';
    const SYSTEM_ERROR            = 'netgsm::errors.system_error';
    const NETGSM_GENERAL_ERROR    = 'netgsm::errors.netgsm_general_error';
    const NO_RECORD               = 'netgsm::errors.no_record';
    const JOB_ID_NOT_FOUND        = 'netgsm::errors.job_id_not_found';
}
