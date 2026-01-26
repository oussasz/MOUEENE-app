<?php
/**
 * Validator Helper Class
 * Handles data validation
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 */

class Validator {
    
    private $errors = [];
    private $data = [];
    
    /**
     * Constructor
     * 
     * @param array $data Data to validate
     */
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    /**
     * Validate required field
     * 
     * @param string $field Field name
     * @param string $message Custom error message
     * @return self
     */
    public function required($field, $message = null) {
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field] = $message ?? ucfirst($field) . ' is required';
        }
        return $this;
    }
    
    /**
     * Validate email
     * 
     * @param string $field Field name
     * @param string $message Custom error message
     * @return self
     */
    public function email($field, $message = null) {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? 'Invalid email format';
        }
        return $this;
    }
    
    /**
     * Validate minimum length
     * 
     * @param string $field Field name
     * @param int $min Minimum length
     * @param string $message Custom error message
     * @return self
     */
    public function minLength($field, $min, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must be at least {$min} characters";
        }
        return $this;
    }
    
    /**
     * Validate maximum length
     * 
     * @param string $field Field name
     * @param int $max Maximum length
     * @param string $message Custom error message
     * @return self
     */
    public function maxLength($field, $max, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must not exceed {$max} characters";
        }
        return $this;
    }
    
    /**
     * Validate numeric value
     * 
     * @param string $field Field name
     * @param string $message Custom error message
     * @return self
     */
    public function numeric($field, $message = null) {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' must be numeric';
        }
        return $this;
    }
    
    /**
     * Validate minimum value
     * 
     * @param string $field Field name
     * @param float $min Minimum value
     * @param string $message Custom error message
     * @return self
     */
    public function min($field, $min, $message = null) {
        if (isset($this->data[$field]) && $this->data[$field] < $min) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must be at least {$min}";
        }
        return $this;
    }
    
    /**
     * Validate maximum value
     * 
     * @param string $field Field name
     * @param float $max Maximum value
     * @param string $message Custom error message
     * @return self
     */
    public function max($field, $max, $message = null) {
        if (isset($this->data[$field]) && $this->data[$field] > $max) {
            $this->errors[$field] = $message ?? ucfirst($field) . " must not exceed {$max}";
        }
        return $this;
    }
    
    /**
     * Validate field matches another field
     * 
     * @param string $field Field name
     * @param string $matchField Field to match
     * @param string $message Custom error message
     * @return self
     */
    public function match($field, $matchField, $message = null) {
        if (isset($this->data[$field]) && isset($this->data[$matchField]) && 
            $this->data[$field] !== $this->data[$matchField]) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' must match ' . ucfirst($matchField);
        }
        return $this;
    }
    
    /**
     * Validate regex pattern
     * 
     * @param string $field Field name
     * @param string $pattern Regex pattern
     * @param string $message Custom error message
     * @return self
     */
    public function pattern($field, $pattern, $message = null) {
        if (isset($this->data[$field]) && !preg_match($pattern, $this->data[$field])) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' format is invalid';
        }
        return $this;
    }
    
    /**
     * Validate value is in array
     * 
     * @param string $field Field name
     * @param array $values Allowed values
     * @param string $message Custom error message
     * @return self
     */
    public function in($field, $values, $message = null) {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values)) {
            $this->errors[$field] = $message ?? ucfirst($field) . ' must be one of: ' . implode(', ', $values);
        }
        return $this;
    }
    
    /**
     * Validate date format
     * 
     * @param string $field Field name
     * @param string $format Date format
     * @param string $message Custom error message
     * @return self
     */
    public function date($field, $format = 'Y-m-d', $message = null) {
        if (isset($this->data[$field])) {
            $d = DateTime::createFromFormat($format, $this->data[$field]);
            if (!$d || $d->format($format) !== $this->data[$field]) {
                $this->errors[$field] = $message ?? ucfirst($field) . ' must be a valid date';
            }
        }
        return $this;
    }
    
    /**
     * Validate phone number
     * 
     * @param string $field Field name
     * @param string $message Custom error message
     * @return self
     */
    public function phone($field, $message = null) {
        if (isset($this->data[$field]) && !preg_match(REGEX_PHONE, $this->data[$field])) {
            $this->errors[$field] = $message ?? 'Invalid phone number format';
        }
        return $this;
    }
    
    /**
     * Check if validation passed
     * 
     * @return bool
     */
    public function passes() {
        return empty($this->errors);
    }
    
    /**
     * Check if validation failed
     * 
     * @return bool
     */
    public function fails() {
        return !$this->passes();
    }
    
    /**
     * Get validation errors
     * 
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get validated data
     * 
     * @return array
     */
    public function getData() {
        return $this->data;
    }
    
    /**
     * Get specific field value
     * 
     * @param string $field Field name
     * @param mixed $default Default value
     * @return mixed
     */
    public function get($field, $default = null) {
        return $this->data[$field] ?? $default;
    }
}
