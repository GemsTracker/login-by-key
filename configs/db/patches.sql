-- GEMS VERSION: 66
-- PATCH: Add login key and valid until to user passwords field
ALTER TABLE `gems__user_passwords`
    ADD `gup_login_key` varchar(255) COLLATE 'utf8_general_ci' NULL AFTER `gup_last_pwd_change`,
    ADD `gup_login_key_valid_until` timestamp NULL AFTER `gup_login_key`;
