-- Restrict gender values to male/female only.
--
-- This migration:
-- 1) Normalizes existing non-binary/legacy values to NULL.
-- 2) Restricts enum to ('male','female') for both users and providers.

UPDATE users
SET gender = NULL
WHERE gender IS NOT NULL
  AND gender NOT IN ('male', 'female');

UPDATE users
SET gender = NULL
WHERE gender = '';

UPDATE providers
SET gender = NULL
WHERE gender IS NOT NULL
  AND gender NOT IN ('male', 'female');

UPDATE providers
SET gender = NULL
WHERE gender = '';

ALTER TABLE users
  MODIFY gender ENUM('male', 'female') NULL DEFAULT NULL;

ALTER TABLE providers
  MODIFY gender ENUM('male', 'female') NULL DEFAULT NULL;
