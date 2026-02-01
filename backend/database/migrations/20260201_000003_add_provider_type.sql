-- Add provider_type to providers table
ALTER TABLE providers
  ADD COLUMN provider_type ENUM('freelancer','self_employed','company')
  DEFAULT 'freelancer'
  AFTER insurance_details;
