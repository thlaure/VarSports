<?php

namespace App\Constant;

class Message
{
    public const GENERIC_SUCCESS = 'Succès';
    public const SUCCESS_VALIDATION = 'Votre club est maintenant en attente de validation';
    public const GENERIC_ERROR = 'Une erreur est survenue';
    public const GENERIC_ENTITY_FIELD_ERROR = 'Les données ne sont pas valides';
    public const GENERIC_FILE_FORM_ERROR = 'Le fichier n\'est pas valide';
    public const GENERIC_GRANT_ERROR = 'Vous n\'avez pas les droits nécessaires';
    public const ACCOUNT_ALREADY_EXISTS = 'Il existe déjà un compte avec cette adresse e-mail.';
    public const DATA_MUST_BE_SET = 'La donnée doit être renseignée';
    public const DATA_NOT_FOUND = 'La données n\'existe pas';
    public const CONSULT_MAILBOX_TO_CONFIRM = 'Veuillez consulter votre messagerie pour confirmer votre adresse e-mail';
    public const ERROR_WHILE_CONFIRM_EMAIL = 'Une erreur est survenue lors de la verification de votre adresse e-mail';
    public const CONFIRM_EMAIL = 'Confirmer votre adresse e-mail';
    public const EMAIL_VERIFIED = 'Votre adresse e-mail a été vérifiée';
    public const GENERIC_ACCESS_DENIED = 'Accès interdit';
    public const CLUB_ALREADY_EXISTS_FOR_THIS_ACCOUNT = 'Un club existe déjà pour ce compte';
    public const CLUB_NOT_FOUND = 'Le club n\'existe pas';
    public const FILE_NOT_FOUND = 'Le fichier n\'a pas été trouvé';
    public const FILE_NOT_READABLE = 'Le fichier n\'a pas pu être lu';
    public const FILE_NOT_WRITABLE = 'Le fichier n\'a pas pu être écrit';
    public const PASSWORD_UPDATED = 'Mot de passe mis à jour';
    public const INVALID_CREDENTIALS = 'Identifiant ou mot de passe invalide';
    public const EMAIL_SUBJECT_CREATE_CLUB = 'Demande de création de club';
    public const TITLE_CREATE_CLUB = 'Créer un club';
    public const TITLE_EDIT_CLUB = 'Modifier le club';
    public const ERROR_CLUB_HAS_NO_EMAIL = 'Le club n\'a pas d\'e-mail';
    public const TITLE_CREATE_EVENT = 'Créer un évènement';
    public const TITLE_EDIT_EVENT = 'Modifier l\'évènement';
    public const RESET_PASSWORD = 'Réinitialisation du mot de passe';
}
