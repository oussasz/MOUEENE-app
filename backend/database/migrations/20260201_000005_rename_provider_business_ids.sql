-- Rename provider business identifier columns
ALTER TABLE providers
  CHANGE COLUMN business_license commercial_registry_number VARCHAR(100),
  CHANGE COLUMN tax_id nif VARCHAR(100),
  CHANGE COLUMN insurance_details nis TEXT;
