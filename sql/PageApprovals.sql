CREATE TABLE /*_*/approval_log (
    al_id INTEGER PRIMARY KEY AUTOINCREMENT,
    al_page_id INTEGER UNSIGNED NOT NULL,
    al_timestamp BINARY(14) NOT NULL,
    al_is_approved TINYINT(1) NOT NULL,
    al_user_id INTEGER UNSIGNED NULL
) /*$wgDBTableOptions*/;

CREATE INDEX idx_page_timestamp ON /*_*/approval_log (al_page_id, al_timestamp);

CREATE TABLE /*_*/approved_html (
    ah_page_id INTEGER UNSIGNED PRIMARY KEY,
    ah_html MEDIUMBLOB NOT NULL,
    ah_timestamp BINARY(14) NOT NULL
) /*$wgDBTableOptions*/;

CREATE TABLE /*_*/approver_config (
    ac_user_id INTEGER UNSIGNED PRIMARY KEY,
    ac_categories BLOB NOT NULL
) /*$wgDBTableOptions*/;
