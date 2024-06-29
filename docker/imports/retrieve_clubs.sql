SELECT u.ID, u.display_name AS name,
    u.user_email AS email,
    u.user_nicename AS slug,
	(SELECT meta_value FROM wp_usermeta um WHERE um.user_id = u.ID AND um.meta_key = 'description') AS description,
    (SELECT meta_value FROM wp_usermeta um WHERE um.user_id = u.ID AND um.meta_key = 'contact_club_nom') AS lastname,
    (SELECT meta_value FROM wp_usermeta um WHERE um.user_id = u.ID AND um.meta_key = 'contact_club_prenom') AS firstname,
    (SELECT meta_value FROM wp_usermeta um WHERE um.user_id = u.ID AND um.meta_key = 'facebook') AS facebook,
    (SELECT meta_value FROM wp_usermeta um WHERE um.user_id = u.ID AND um.meta_key = 'instagram') AS instagram,
    (SELECT meta_value FROM wp_usermeta um WHERE um.user_id = u.ID AND um.meta_key = 'youtube') AS youtube,
    (SELECT meta_value FROM wp_usermeta um WHERE um.user_id = u.ID AND um.meta_key = 'twitter') AS twitter,
    (SELECT meta_value FROM wp_usermeta um WHERE um.user_id = u.ID AND um.meta_key = 'site-internet') AS website,
    (SELECT meta_value FROM wp_usermeta um WHERE um.user_id = u.ID AND um.meta_key = 'club_adresse') AS address,
    (SELECT meta_value FROM wp_usermeta um WHERE um.user_id = u.ID AND um.meta_key = 'club_code_postal') AS postal_code,
    (SELECT meta_value FROM wp_usermeta um WHERE um.user_id = u.ID AND um.meta_key = 'club_ville_select') AS city,
    (SELECT meta_value FROM wp_usermeta um WHERE um.user_id = u.ID AND um.meta_key = 'contact_club_tel') AS phone,
    (SELECT meta_value FROM wp_usermeta um WHERE um.user_id = u.ID AND um.meta_key = 'contact_club_mail') AS admin_email,
    (SELECT meta_value FROM wp_usermeta um WHERE um.user_id = u.ID AND um.meta_key = 'club_disciplines') AS disciplines
FROM wp_users u;