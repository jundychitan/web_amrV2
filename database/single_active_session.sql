-- Apply to the application's MySQL database BEFORE deploying the PHP changes.
-- Existing authenticated browsers must log in again after deployment.
-- No data is changed automatically by the application at deployment time.
ALTER TABLE user_tb
    ADD COLUMN active_session_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL,
    ADD COLUMN active_session_activity BIGINT UNSIGNED NULL DEFAULT NULL;

-- Lock expiry is enforced at request/login time; no scheduled cleanup is needed.
-- Rollback (only after reverting the PHP changes):
-- ALTER TABLE user_tb DROP COLUMN active_session_hash, DROP COLUMN active_session_activity;
