<?php
/**
 * Bootstrap file for XmlToArray library
 * This ensures all classes are loaded in the correct namespace context
 */

// Only load once
if (!class_exists('Clearstream\XmlToArray\XmlToArray')) {
    // Load all required files in proper order
    require_once __DIR__ . '/XmlToArrayException.php';
    require_once __DIR__ . '/XmlToArrayConfig.php'; 
    require_once __DIR__ . '/XmlToArrayConverter.php';
    require_once __DIR__ . '/XmlToArray.php';
    
    // Verify all classes are loaded
    if (!class_exists('Clearstream\XmlToArray\XmlToArrayException')) {
        throw new Exception('Failed to load XmlToArrayException class');
    }
    if (!class_exists('Clearstream\XmlToArray\XmlToArrayConfig')) {
        throw new Exception('Failed to load XmlToArrayConfig class');
    }
    if (!class_exists('Clearstream\XmlToArray\XmlToArrayConverter')) {
        throw new Exception('Failed to load XmlToArrayConverter class');
    }
    if (!class_exists('Clearstream\XmlToArray\XmlToArray')) {
        throw new Exception('Failed to load XmlToArray class');
    }
}