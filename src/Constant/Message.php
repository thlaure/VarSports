<?php

namespace App\Constant;

class Message
{
    public const GENERIC_SUCCESS = 'generic_success';
    public const SUCCESS_BEFORE_CLUB_VALIDATION = 'success_before_club_validation';
    public const SUCCESS_BEFORE_EVENT_VALIDATION = 'success_before_event_validation';
    public const GENERIC_ERROR = 'generic_error';
    public const GENERIC_ENTITY_FIELD_ERROR = 'generic_entity_validation_error';
    public const GENERIC_FILE_FORM_ERROR = 'file_invalid';
    public const GENERIC_GRANT_ERROR = 'grant_denied';
    public const ACCOUNT_ALREADY_EXISTS = 'account_already_exists_with_this_email';
    public const DATA_MUST_BE_SET = 'data_must_be_set';
    public const DATA_NOT_FOUND = 'data_does_not_exist';
    public const CONSULT_MAILBOX_TO_CONFIRM = 'consult_mailbox_to_confirm';
    public const ERROR_WHILE_CONFIRM_EMAIL = 'error_verification_email';
    public const CONFIRM_EMAIL = 'confirm_email';
    public const EMAIL_VERIFIED = 'email_verified';
    public const GENERIC_ACCESS_DENIED = 'access_denied';
    public const CLUB_ALREADY_EXISTS_FOR_THIS_ACCOUNT = 'club_already_exists_for_this_account';
    public const CLUB_NOT_FOUND = 'club_not_found';
    public const FILE_NOT_FOUND = 'file_not_found';
    public const FILE_NOT_READABLE = 'file_not_readable';
    public const FILE_NOT_WRITABLE = 'file_not_writable';
    public const PASSWORD_UPDATED = 'password_updated';
    public const INVALID_CREDENTIALS = 'invalid_credentials';
    public const EMAIL_SUBJECT_CREATE_CLUB = 'email_subject_create_club';
    public const EMAIL_SUBJECT_CREATE_EVENT = 'email_subject_create_event';
    public const TITLE_CREATE_CLUB = 'create_club';
    public const TITLE_EDIT_CLUB = 'edit_club';
    public const ERROR_CLUB_HAS_NO_EMAIL = 'club_has_no_email';
    public const TITLE_CREATE_EVENT = 'create_event';
    public const TITLE_EDIT_EVENT = 'edit_event';
    public const RESET_PASSWORD = 'reset_password';
    public const CONTACT_FORM = 'contact_form';
}
